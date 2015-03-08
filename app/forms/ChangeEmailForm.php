<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\Submit;

class ChangeEmailForm extends Form {

    public function initialize($user) {

        $current_email = new Email('current_email', array(
            'placeholder' => 'Current Email',
            'value' => $user->email
        ));

        $this->add($current_email);

        $new_email = new Email('new_email', array(
            'placeholder' => 'New Email',
            'maxlength' => 50
        ));

        $this->add($new_email);

        $repeat_email = new Email('repeat_email', array(
            'placeholder' => 'Repeat Email',
            'maxlength' => 50
        ));

        $this->add($repeat_email);

        $submit = new Submit('Change Email', array(
            'value' => 'Change Email',
            'class' => 'btn btn-primary'
        ));

        $this->add($submit);

    }

}