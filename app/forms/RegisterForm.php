<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Email;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Filter;

class RegisterForm extends Form {

    public function initialize() {

        // create new username element
        $username = new Text('username', array(
            'maxlength' => 30,
            'placeholder' => 'Username'
        ));

        $username->addValidators(array(
                new PresenceOf(array(
                    'message' => 'Usernname is required'
                )))
        );

        $username->addValidators(array(
                new StringLength(array(
                    'min' => 3,
                    'max' => 20
                )))
        );

        $this->add($username);

        // create new email element
        $email = new Email('email', array(
            'maxlength' => 50,
            'placeholder' => 'Email'
        ));

        $email->addValidators(array(
                new PresenceOf(array(
                    'message' => 'Email is required'
                )))
        );

        $email->addFilter('email');

        $email->addValidators(array(
                new StringLength(array(
                    'min' => 3,
                    'max' => 50
                )))
        );

        $this->add($email);

        // create new password element
        $password = new Password('password', array(
            'maxlength' => 16,
            'placeholder' => 'Password'
        ));

        $password->addValidators(array(
                new PresenceOf(array(
                    'message' => 'The password is required'
                )))
        );

        $password->addValidators(array(
                new StringLength(array(
                    'min' => 6,
                    'max' => 32
                )))
        );

        $this->add($password);

        // create new repeat password element
        $repeat = new Password('repeat', array(
            'maxlength' => 16,
            'placeholder' => 'Repeat password'
        ));

        $repeat->addValidators(array(
                new PresenceOf(array(
                    'message' => 'You must repeat the password'
                )))
        );

        $repeat->addValidators(array(
                new StringLength(array(
                    'min' => 6,
                    'max' => 32
                )))
        );

        $repeat->addValidators(array(
                new Confirmation(array(
                    'message' => 'The passwords must match',
                    'with' => 'password'
                )))
        );

        $this->add($repeat);

        // create new submit element
        $submit = new Submit('Register', array(
            'class' => 'btn btn-success'
        ));

        $this->add($submit);

    }

}