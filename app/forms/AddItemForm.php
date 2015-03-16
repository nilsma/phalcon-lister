<?php

use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Form,
    Phalcon\Forms\Element\Text,
    Phalcon\Forms\Element\Hidden;

class AddItemForm extends Form {

    public function initialize($list) {

        // Item name element
        $item_name = new Text('item_name', array(
            'maxlength' => 200,
            'placeholder' => 'Item name'
        ));

        $item_name->addValidator(
            new PresenceOf(array(
                'message' => 'Item name is required'
            ))
        );

        $this->add($item_name);

        // Working list element
        $working_list = new Hidden('working_list', array(
            'name' => 'working_list',
            'value' => $list->id
        ));

        $this->add($working_list);

        // Submit element
        $submit = new Submit('Add', array(
            'class' => 'btn btn-primary pull-right'
        ));

        $this->add($submit);

    }

}