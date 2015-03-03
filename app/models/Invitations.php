<?php

class Invitations extends \Phalcon\Mvc\Model
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
    public $invited_id;

}
