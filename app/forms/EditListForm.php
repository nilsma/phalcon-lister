<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;

class EditListForm extends Form {

    public function initialize($list) {

        $current_title = new Text('current_title', array(
            'value' => $list->title,
            'disabled' => 'disabled'
        ));

        $current_title->setLabel('Current Title:');

        $this->add($current_title);

        $new_title = new Text('new_title', array(
            'placeholder' => 'Enter New Title',
            'maxlength' => 50
        ));

        $new_title->setLabel('New Title:');

        $this->add($new_title);

        $list_id = new Hidden('list_id', array(
            'value' => $list->id
        ));

        $this->add($list_id);

        $submit = new Submit('Save', array(
            'class' => 'btn btn-success'
        ));

        $this->add($submit);

    }

}