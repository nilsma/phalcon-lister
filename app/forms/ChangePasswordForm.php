<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;

class ChangePasswordForm extends Form {

    public function initialize() {

        $current_password = new Password('current_password', array(
            'placeholder' => 'Enter password',
            'maxlength' => 16
        ));

        $this->add($current_password);

        $new_password = new Password('new_password', array(
            'placeholder' => 'New password',
            'maxlength' => 16
        ));

        $this->add($new_password);

        $repeat_password = new Password('repeat_password', array(
            'placeholder' => 'Repeat password',
            'maxlength' => 16
        ));

        $this->add($repeat_password);

        $submit = new Submit('Change Password', array(
            'value' => 'Change Password',
            'class' => 'btn btn-primary'
        ));

        $this->add($submit);

    }

}