<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Select;

class ListSelectForm extends Form {

    public function initialize($user, $list) {

        $select = new Select('select', Lists::findLists($user->id), array(
            'using' => array('id', 'title'),
            'name' => 'list',
            'class' => 'form-control',
            'id' => 'select_list'
        ));

        $select->setDefault($list->id);

        $this->add($select);

    }

}