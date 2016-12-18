<?php

class GuestUser_UserController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('User');
        $this->_auth = $this->getInvokeArg('bootstrap')->getResource('Auth');
    }

    /**
     * The browse action.
     */
    public function browseAction()
    {
        parent::browseAction();

        $this->view->usersDetails = $this->_helper->db->getTable('GuestUserDetail')
            ->findByUsers($this->view->users);
    }

    public function loginAction()
    {
        $session = new Zend_Session_Namespace;
        if(!$session->redirect) {
            $session->redirect = $_SERVER['HTTP_REFERER'];
        }

        $this->redirect('users/login');
    }

    public function registerAction()
    {

        if(current_user()) {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }
        $openRegistration = (get_option('guest_user_open') == 1);
        $instantAccess = (get_option('guest_user_instant_access') == 1);
        $user = new User();

        $form = $this->_getForm(array('user'=>$user));
        $this->view->form = $form;

        if (!$this->getRequest()->isPost() || !$form->isValid($_POST)) {
            return;
        }
        $userFields = $this->_getAdditionalFieldsFromPost();
        $user->role = 'guest';
        if($openRegistration || $instantAccess) {
            $user->active = true;
        }
        $user->setPassword($_POST['new_password']);
        $user->setPostData($_POST);
        try {
            if ($user->save()) {
                $this->_saveAdditionalFields($user, $userFields);
                $token = $this->_createToken($user);
                $this->_sendConfirmationEmail($user, $token); //confirms that they registration request is legit
                if($instantAccess) {
                    //log them right in, and return them to the previous page
                    $authAdapter = new Omeka_Auth_Adapter_UserTable($this->_helper->db->getDb());
                    $authAdapter->setIdentity($user->username)->setCredential($_POST['new_password']);
                    $authResult = $this->_auth->authenticate($authAdapter);
                    if (!$authResult->isValid()) {
                        $log = $this->_getLog();
                        if ($log) {
                            $ip = $this->getRequest()->getClientIp();
                            $log->info(__("Failed login attempt from %s", $ip));
                        }
                        $this->_helper->flashMessenger($this->getLoginErrorMessages($authResult), 'error');
                        return;
                                        }
                    $activation = UsersActivations::factory($user);
                    $activation->save();
                    $this->_helper->flashMessenger(__("You are logged in temporarily. Please check your email for a confirmation message. Once you have confirmed your request, you can log in without time limits."));
                    $session = new Zend_Session_Namespace;
                    if ($session->redirect) {
                        $this->_helper->redirector->gotoUrl($session->redirect);
                    }
                    return;
                }
                if($openRegistration) {
                    $message = __("Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request, you will be able to log in.");
                    $this->_helper->flashMessenger($message, 'success');
                    $activation = UsersActivations::factory($user);
                    $activation->save();

                } else {
                    $message = __("Thank you for registering. Please check your email for a confirmation message. Once you have confirmed your request and an administrator activates your account, you will be able to log in.");
                    $this->_helper->flashMessenger($message, 'success');
                }
            }
        } catch (Omeka_Validate_Exception $e) {
            $this->flashValidationErrors($e);
        }
    }

    public function updateAccountAction()
    {
        $user = current_user();

        $form = $this->_getForm(array('user'=>$user));
        $form->getElement('new_password')->setLabel(__("New Password"));
        $form->getElement('new_password')->setRequired(false);
        $form->getElement('new_password_confirm')->setRequired(false);
        $form->addElement('password', 'current_password',
                        array(
                                'label'         => __('Current Password'),
                                'required'      => true,
                                'class'         => 'textinput',
                        )
        );

        $oldPassword = $form->getElement('current_password');
        $oldPassword->setOrder(0);
        $form->addElement($oldPassword);

        $form->setDefaults($user->toArray());
        $this->view->form = $form;

        if (!$this->getRequest()->isPost() || !$form->isValid($_POST)) {
            return;
        }

        if($user->password != $user->hashPassword($_POST['current_password'])) {
            $this->_helper->flashMessenger(__("Incorrect password"), 'error');
            return;
        }

        $userFields = $this->_getAdditionalFieldsFromPost();

        $user->setPassword($_POST['new_password']);
        $user->setPostData($_POST);

        try {
            $user->save($_POST);
            $this->_saveAdditionalFields($user, $userFields);
        } catch (Omeka_Validate_Exception $e) {
            $this->flashValidationErrors($e);
        }
    }

    public function meAction()
    {
        $user = current_user();
        if(!$user) {
            $this->redirect('/');
        }
        $widgets = array();
        $widgets = apply_filters('guest_user_widgets', $widgets);
        $this->view->widgets = $widgets;
    }

    public function staleTokenAction()
    {
        $auth = $this->getInvokeArg('bootstrap')->getResource('Auth');
        $auth->clearIdentity();
    }

    public function confirmAction()
    {
        $db = get_db();
        $token = $this->getRequest()->getParam('token');
        $records = $db->getTable('GuestUserToken')->findBy(array('token'=>$token));
        $record = $records[0];
        if($record) {
            $record->confirmed = true;
            $record->save();
            $user = $db->getTable('User')->find($record->user_id);
            $this->_sendAdminNewConfirmedUserEmail($user);
            $this->_sendConfirmedEmail($user);
            if(get_option('guest_user_open') == 1) {
                $message = __("You can now log in using the password you chose.");
            } else {
                $message = __("Please check the email we just sent you for the next steps! You're almost there!");
            }
            $this->_helper->flashMessenger($message, 'success');
            $this->redirect('users/login');
        } else {
            $this->_helper->flashMessenger(__('Invalid token'), 'error');
        }
    }

    protected function _getForm($options)
    {
        require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'GuestUser.php';
        $options['userFields'] = $this->_loadAdditionalFields($options['user']);
        return new GuestUser_Form_GuestUser($options);
    }

    protected function _getAdditionalFieldsFromPost()
    {
        $additionalFields = json_decode(get_option('guest_user_fields'), true);
        $userFields = array_fill_keys(array_keys($additionalFields), '');
        foreach ($userFields as $name => &$value) {
            $fieldValue = trim($_POST[$name]);
            if (strlen($fieldValue) > 0) {
                $value = $fieldValue;
            }
        }
        return $userFields;
    }

    protected function _loadAdditionalFields($user)
    {
        $db = $this->_helper->db;
        $details = $db->getTable('GuestUserDetail')->findByUsers($user);
        return empty($details)
            ? array()
            : $details->getValues();
    }

    protected function _saveAdditionalFields($user, $userFields)
    {
        $db = $this->_helper->db;
        $details = $db->getTable('GuestUserDetail')->findByUsers($user);
        if (empty($details)) {
            $details = new GuestUserDetail();
            $details->user_id = $user->id;
        }
        $details->setValues($userFields);
        $details->save();
    }

    protected function _sendConfirmedEmail($user)
    {
        $siteTitle = get_option('site_title');
        $body = __("Thanks for joining %s!", $siteTitle);
        $siteUrl = absolute_url('/');
        if(get_option('guest_user_open') == 1) {
            $body .= "<p>" . __("You can now log into %s using the password you chose.", "<a href='$siteUrl'>$siteTitle</a>") . "</p>";
        } else {
            $body .= "<p>" . __("When an administrator approves your account, you will receive another message that you can use to log in with the password you chose.") . "</p>";
        }

        $subject = __("Registration for %s", $siteTitle);
        $mail = $this->_getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
        }
    }

    protected function _sendConfirmationEmail($user, $token)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . '/guest-user/user/confirm/token/' . $token->token;
        $siteUrl = absolute_url('/');
        $subject = __("Your request to join %s", $siteTitle);
        $body = __("You have registered for an account on %s. Please confirm your registration by following %s.  If you did not request to join %s please disregard this email.", "<a href='$siteUrl'>$siteTitle</a>", "<a href='$url'>" . __('this link') . "</a>", $siteTitle);

        if(get_option('guest_user_instant_access') == 1) {
            $body .= "<p>" . __("You have temporary access to %s for twenty minutes. You will need to confirm your request to join after that time.", $siteTitle) . "</p>";
        }
        $mail = $this->_getMail($user, $body, $subject);
        try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
        }
    }

    protected function _sendAdminNewConfirmedUserEmail($user)
    {
        $siteTitle = get_option('site_title');
        $url = WEB_ROOT . "/admin/users/edit/" . $user->id;
        $subject = __("New request to join %s", $siteTitle);
        $body = "<p>" . __("A new user has confirmed that they want to join %s : %s" , $siteTitle, "<a href='$url'>" . $user->username . "</a>") . "</p>";
        if(get_option('guest_user_open') !== 1) {
            if(get_option('guest_user_instant_access') == 1) {
                $body .= "<p>" . __("%s has temporary access to the site.", $user->username) . "</p>";
            }
            $body .= "<p>" . __("You will need to make the user active and save the changes to complete registration for %s.", $user->username) . "</p>";
        }

        $mail = $this->_getMail($user, $body, $subject);
        $mail->clearRecipients();
        $mail->addTo(get_option('administrator_email'), "$siteTitle Administrator");
         try {
            $mail->send();
        } catch (Exception $e) {
            _log($e);
        }
    }

    protected function _getMail($user, $body, $subject)
    {
        $siteTitle  = get_option('site_title');
        $from = get_option('administrator_email');
        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($body);
        $mail->setFrom($from, __("%s Administrator", $siteTitle));
        $mail->addTo($user->email, $user->name);
        $mail->setSubject($subject);
        $mail->addHeader('X-Mailer', 'PHP/' . phpversion());
        return $mail;
    }

    protected function _createToken($user)
    {
        $token = new GuestUserToken();
        $token->user_id = $user->id;
        $token->token = sha1("tOkenS@1t" . microtime());
        if(method_exists($user, 'getEntity')) {
            $token->email = $user->getEntity()->email;
        } else {
            $token->email = $user->email;
        }
        $token->save();
        return $token;
    }
}

