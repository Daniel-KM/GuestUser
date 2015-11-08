<fieldset id="fieldset-guest-user-interface">
    <legend><?php echo __('Interface Display'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_capabilities',
                __('Registration Features')); ?>
        </div>
        <div class="inputs five columns omega" >
            <p class='explanation'>
                <?php echo __('Add some text to the registration screen so people will know what they get for registering.'
                    . ' ' . 'As you enable and configure plugins that make use of the guest user, please give them guidance about what they can and cannot do.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formTextarea('guest_user_capabilities', get_option('guest_user_capabilities'), array('rows' => 3)); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_short_capabilities',
                __('Short Registration Features')); ?>
        </div>
        <div class="inputs five columns omega" >
            <p class='explanation'>
                <?php echo __('Add a shorter version to use as a dropdown from the user bar. If empty, no dropdown will appear.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formTextarea('guest_user_short_capabilities', get_option('guest_user_capabilities'), array('rows' => 3)); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_dashboard_label',
                __('Dashboard Label')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class='explanation'>
                <?php echo __("The text to use for the label on the user's dashboard."); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formText('guest_user_dashboard_label', get_option('guest_user_dashboard_label'), null); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_login_text',
                __('Login Text')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class='explanation'>
                <?php echo __('The text to use for the "Login" link in the user bar.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formText('guest_user_login_text', get_option('guest_user_login_text'), null); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_register_text',
                __('Register Text')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class='explanation'>
                <?php echo __('The text to use for the "Register" link in the user bar.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formText('guest_user_register_text', get_option('guest_user_register_text'), null); ?>
            </div>
        </div>
    </div>
</fieldset>

<fieldset id="fieldset-guest-user-mode">
    <legend><?php echo __('Mode of Registration'); ?></legend>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_open',
                __('Allow open registration?')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Allow guest user registration without administrator approval.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formCheckbox('guest_user_open', true, array('checked' => (boolean) get_option('guest_user_open'))); ?>
            </div>
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_instant_access',
                __('Allow instant access')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Allow instant access for 20 minutes for new users.'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formCheckbox('guest_user_instant_access', true, array('checked' => (boolean) get_option('guest_user_instant_access'))); ?>
            </div>
        </div>
    </div>

    <?php if (get_option('recaptcha_public_key') && get_option('recaptcha_private_key')): ?>
    <div class="field">
        <div class="two columns alpha">
            <?php echo $this->formLabel('guest_user_recaptcha',
                __('Require ReCaptcha')); ?>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">
                <?php echo __('Check this to require passing a ReCaptcha test when registering'); ?>
            </p>
            <div class="input-block">
                <?php echo $this->formCheckbox('guest_user_recaptcha', true, array('checked' => (boolean) get_option('guest_user_recaptcha'))); ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <p>
        <?php echo __('You have not set up ReCaptcha keys in the security settings. We strongly recommend using ReCaptcha to prevent spam account creation.'); ?>
    </p>
    <?php endif; ?>
</fieldset>
