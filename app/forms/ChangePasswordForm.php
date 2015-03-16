<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;

class ChangePasswordForm extends Form {

    public function initialize() {

        // Create current password element
        $current_password = new Password('current_password', array(
            'placeholder' => 'Enter password',
            'maxlength' => 16
        ));

        $current_password->addValidator(
            new PresenceOf(array(
                'message' => 'Current password is required'
            ))
        );

        $this->add($current_password);

        // create new password element
        $new_password = new Password('new_password', array(
            'placeholder' => 'New password',
            'maxlength' => 16
        ));

        $new_password->addValidator(
            new PresenceOf(array(
                'message' => 'You have to enter a new password'
            ))
        );

        $new_password->addValidator(
            new StringLength(array(
                'min' => 6,
                'max' => 16
            ))
        );

        $this->add($new_password);

        // create repeat password element
        $repeat_password = new Password('repeat_password', array(
            'placeholder' => 'Repeat password',
            'maxlength' => 16
        ));

        $repeat_password->addValidator(
            new PresenceOf(array(
                'message' => 'You have to enter a new password'
            ))
        );

        $repeat_password->addValidator(
            new StringLength(array(
                'min' => 6,
                'max' => 16
            ))
        );

        $repeat_password->addValidator(
            new Confirmation(array(
                'message' => 'The passwords must match',
                'with' => 'new_password'
            ))
        );

        $this->add($repeat_password);

        // create submit element
        $submit = new Submit('Change Password', array(
            'value' => 'Change Password',
            'class' => 'btn btn-primary'
        ));

        $this->add($submit);

    }

}