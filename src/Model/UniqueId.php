<?php
namespace Eshop\Model;

trait UniqueId {

    protected static $sequence = 0;
    private $id;

    function createID()
    {
        $this->id = ++static::$sequence;
    }

    /**
     * @param $id int
     */
    function setID($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    function getID()
    {
        return $this->id;
    }
}