<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Submit;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;

class ChangeEmailForm extends Form {

    public function initialize($user) {

        // current email element
        $current_email = new Email('current_email', array(
            'placeholder' => 'Current Email',
            'value' => $user->email
        ));

        $current_email->addValidator(
            new PresenceOf(array(
                'message' => 'You have to state your current email'
            ))
        );

        $this->add($current_email);

        // New email element
        $new_email = new Email('new_email', array(
            'placeholder' => 'New Email',
            'maxlength' => 50
        ));

        $new_email->addValidator(
            new StringLength(array(
                'max' => 50,
                'min' => 7
            ))
        );

        $this->add($new_email);

        // Repeat email element
        $repeat_email = new Email('repeat_email', array(
            'placeholder' => 'Repeat Email',
            'maxlength' => 50
        ));

        $repeat_email->addValidator(
            new StringLength(array(
                'max' => 50,
                'min' => 7
            ))
        );

        $repeat_email->addValidator(
            new Confirmation(array(
                'message' => 'The emails must match',
                'with' => 'new_email'
            ))
        );

        $this->add($repeat_email);

        // submit element
        $submit = new Submit('Change Email', array(
            'value' => 'Change Email',
            'class' => 'btn btn-primary'
        ));

        $this->add($submit);

    }

}