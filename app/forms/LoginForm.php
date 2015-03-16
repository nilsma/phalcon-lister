<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;


class LoginForm extends Form
{
    public function initialize() {

        // create new username element
        $username = new Text('username', array(
            'placeholder' => 'Username'
        ));

        $username->addValidator(
            new PresenceOf(array(
                'message' => 'Username is required'
            )));

        $this->add($username);

        // create new password element
        $password = new Password('password', array(
            'placeholder' => 'Password'
        ));

        $password->addValidator(new PresenceOf(array(
            'message' => 'The password is required'
        )));

        $this->add($password);

        // create new submit element
        $submit = new Submit('Login', array(
            'class' => 'btn btn-success'
        ));

        $this->add($submit);

    }

}