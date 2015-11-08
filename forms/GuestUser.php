<?php

class GuestUser_Form_GuestUser extends Omeka_Form_User
{
    private $_userFields;

    /**
     * Initialize the form.
     */
    public function init()
    {
        parent::init();

        $additionalFields = $this->_getAdditionalFields();

        // Need to remove submit to add in new elements.
        $this->removeElement('submit');
        $this->addElement('password', 'new_password',
            array(
                'label' => __('Password'),
                'required' => true,
                'class' => 'textinput',
                'validators' => array(
                    array(
                        'validator' => 'NotEmpty',
                        'breakChainOnFailure' => true,
                        'options' => array(
                            'messages' => array(
                                'isEmpty' => __("New password must be entered."),
                            ),
                        ),
                    ),
                    array(
                        'validator' => 'Confirmation',
                        'options' => array(
                            'field' => 'new_password_confirm',
                            'messages' => array(
                                Omeka_Validate_Confirmation::NOT_MATCH => __('New password must be typed correctly twice.'),
                            ),
                         ),
                    ),
                    array(
                        'validator' => 'StringLength',
                        'options' => array(
                            'min' => User::PASSWORD_MIN_LENGTH,
                            'messages' => array(
                                Zend_Validate_StringLength::TOO_SHORT => __("New password must be at least %min% characters long."),
                            ),
                        ),
                    ),
                ),
            )
        );

        $this->addElement('password', 'new_password_confirm',
            array(
                'label' => __('Password again for match'),
                'required' => true,
                'class' => 'textinput',
                'errorMessages' => array(__('New password must be typed correctly twice.')),
            )
        );

        // Add each additional fields, if any.
        // Other options can be set in the theme.
        $defaultParams = array(
            'required' => false,
            'type' => 'text',
            'label' => '',
        );
        foreach ($additionalFields as $fieldName => $fieldParams) {
            $fieldParams = array_merge($defaultParams, $fieldParams);
            $this->addElement($fieldParams['type'], $fieldName, array(
                'label' => $fieldParams['label'],
                // 'description' => $description,
                'value' => isset($this->_userFields[$fieldName]) ? $this->_userFields[$fieldName] : '',
                'size' => '30',
                // Used only for textarea.
                'rows' => 5,
                'required' => $fieldParams['required'],
                // 'validators' => array(),
            ));
        }

        if (Omeka_Captcha::isConfigured() && (get_option('guest_user_recaptcha') == 1)) {
            $this->addElement('captcha', 'captcha',
                array(
                    'class' => 'hidden',
                    'style' => 'display: none;',
                    'label' => __("Please verify you're a human"),
                    'type' => 'hidden',
                    'captcha' => Omeka_Captcha::getCaptcha(),
            ));
        }

        if (current_user()) {
            $submitLabel = __('Update');
        } else {
            $submitLabel = get_option('guest_user_register_text') ?: __('Register');
        }
        $this->addElement('submit', 'submit', array('label' => $submitLabel));
    }

    /**
     * Get additional fields if any.
     *
     * @return array An associative array of the name and the label of
     * additional fields.
     */
    protected function _getAdditionalFields()
    {
        $fields = array();
        $options = get_option('guest_user_fields');
        if (!empty($options)) {
            $fields = json_decode($options, true);
        }
        return $fields;
    }

    /**
     * Set values of user fields if any.
     *
     * @return array An associative array of the name and the value of each
     * additional field.
     */
    public function setUserFields($userFields)
    {
        $this->_userFields = $userFields;
    }
}
