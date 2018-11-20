<?php
namespace Eshop\Model;

/**
 * Class RegisteredCustomer
 * @package Eshop\Model
 */
class RegisteredCustomer extends Customer
{
    const REGISTRATION_LOYALTY_POINTS = 0;
    const LOYALTY_POINTS_COEF = 1;
    protected static $table = 'customer';

    /**
     * RegisteredCustomer constructor.
     * @param null $id int
     * @param null $name string
     */
    public function __construct($id = null, $name = null)
    {
        $this->setID($id);
        $this->setName($name);
        $this->loyaltyPoints = self::REGISTRATION_LOYALTY_POINTS;
    }


    /**
     * @var double
     */
    private $loyaltyPoints;

    /**
     * @return double
     */
    public function getLoyaltyPoints()
    {
        return $this->loyaltyPoints;
    }

    /**
     * @param double $loyaltyPoints
     */
    public function setLoyaltyPoints($loyaltyPoints)
    {
        $this->loyaltyPoints = $loyaltyPoints;
    }

    /**
     * @param double $loyaltyPoints
     */
    public function addLoyaltyPoints($loyaltyPoints)
    {
        $this->loyaltyPoints += $loyaltyPoints;
    }



}