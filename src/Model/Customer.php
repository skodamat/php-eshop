<?php
namespace Eshop\Model;

/**
 * Class Customer
 * @package Eshop\Model
 */
class Customer extends ActiveRecord
{
    use UniqueId;


    /**
     * Customer constructor.
     * @param null $name string
     * @param null $id int
     */
    public function __construct( $name = null, $id = null )
    {
        if( $name ) {
            if ($id) {
                $this->id = $id;
            } else {
                $this->createID();
            }
            $this->name = $name;
        }
    }

    protected static $table = 'customer';

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string
     * @return string
     */
    public function setName($name)
    {
        return $this->name = $name;
    }


    public static function createDbTable()
    {
        self::execute('CREATE TABLE IF NOT EXISTS "customer" ( id INTEGER PRIMARY KEY, name TEXT, loyaltyPoints REAL );');
    }
}