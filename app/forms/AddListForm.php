<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;

class AddListForm extends Form {

    public function initialize() {

        $list_title = new Text('list_title', array(
            'placeholder' => 'List Title',
            'maxlength' => 50
        ));

        $list_title->setLabel('New List Title: ');

        $this->add($list_title);

        $submit = new Submit('Add', array(
            'value' => 'Add',
            'class' => 'btn btn-primary pull-right'
        ));

        $this->add($submit);

    }

}