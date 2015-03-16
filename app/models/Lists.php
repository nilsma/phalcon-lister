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
    public $owner_id;

    /**
     *
     * @var string
     */
    public $title;

    /**
     * Gets the lists that the given user owns or is a member of
     * @param $id - the user id of the given user
     * @return ResultSet - an object containing Lists
     */
    public static function findLists($id) {

        $sql = "";
        $sql .= "(SELECT * FROM lists WHERE owner_id={$id}) ";
        $sql .= "UNION ";
        $sql .= "(SELECT * FROM lists WHERE id in (SELECT list_id FROM members WHERE member_id={$id})) ";
        $sql .= "ORDER BY title";

        $lists = new Lists();
        return new ResultSet(null, $lists, $lists->getReadConnection()->query($sql));
    }

}
