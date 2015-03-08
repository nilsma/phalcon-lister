<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;

class InvitationsForm extends Form {

    public function initialize($lists) {

        $username = new Text('invite_username', array(
            'placeholder' => 'User name',
            'maxlength' => 30
        ));

        $username->setLabel('User to invite:');

        $this->add($username);

        $select = new Select('select', $lists, array(
            'using' => array('id', 'title'),
            'name' => 'list',
            'id' => 'list'
        ));

        $this->add($select);

        $submit = new Submit('Invite', array(
            'value' => 'Invite',
            'class' => 'btn btn-primary'
        ));

        $this->add($submit);

    }

}