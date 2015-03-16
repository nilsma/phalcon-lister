<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;

class InvitationsForm extends Form {

    public function initialize($list) {

        // create new username element
        $username = new Text('invite_username', array(
            'placeholder' => 'User name',
            'maxlength' => 30
        ));

        $username->setLabel('Invite User:');

        $this->add($username);

        // create new list id element
        $list_id = new Hidden('invite_list_id', array(
            'value' => $list->id
        ));

        $this->add($list_id);

        // create new submit element
        $submit = new Submit('Invite', array(
            'value' => 'Invite',
            'class' => 'btn btn-primary pull-right'
        ));

        $this->add($submit);

    }

}