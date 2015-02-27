<?php

class Members extends \Phalcon\Mvc\Model
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
    public $owner_id;

    /**
     *
     * @var integer
     */
    public $list_id;

    /**
     *
     * @var integer
     */
    public $user_id;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('owner_id', 'Users', 'id', NULL);
        $this->belongsTo('list_id', 'Lists', 'id', NULL);
        $this->belongsTo('user_id', 'Users', 'id', NULL);
    }

}
