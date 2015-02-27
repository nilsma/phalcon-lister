<?php

class Items extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var integer
     */
    public $list_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $tapped;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('list_id', 'Lists', 'id', NULL);
    }

}
