<?php
namespace Eshop\Model;

class UnregisteredCustomer extends Customer
{
    /**
     * UnregisteredCustomer constructor.
     * @param null $name string
     * @param null $id int
     */
    public function __construct($name = null, $id = null)
    {
        parent::__construct($name, $id);
    }


    /**
     * @return RegisteredCustomer
     */
    public function register()
    {
        return new RegisteredCustomer($this->getID(), $this->getName());
    }

}