<?php

use Phalcon\Mvc\Model\Resultset\Simple as ResultSet,
    Phalcon\Mvc\Model,
    Phalcon\Mvc\Model\Relation;

class Lists extends \Phalcon\Mvc\Model
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
    public $owner;

    /**
     *
     * @var string
     */
    public $title;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {

        $this->hasMany('id', 'Invitations', 'list_id', NULL);
        $this->hasMany('id', 'Items', 'list_id', NULL);
        $this->hasMany('id', 'Members', 'list_id', NULL);
        $this->belongsTo('owner', 'Users', 'id', NULL);
    }

    public static function findLists($id) {

        $sql = "(SELECT * FROM lists WHERE owner={$id}) UNION (SELECT * FROM lists WHERE id in (SELECT list_id FROM members WHERE user_id={$id})) ORDER BY title";

        $lists = new Lists();

        return new ResultSet(null, $lists, $lists->getReadConnection()->query($sql));
    }

}
