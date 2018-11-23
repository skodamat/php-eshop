<?php
namespace Eshop\Model;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Product
 * @package Eshop\Model
 */
class Product extends ActiveRecord
{
    use UniqueId;

    /**
     * Product constructor.
     * @param $name string
     * @param $price int
     * @param $vatRate double
     * @param $id int
     */
    public function __construct($name = null, $price = null, $vatRate = 0.0, $id = null)
    {
        if ( $name ) {
            $this->name = $name;
            $this->price = $price;
            $this->vatRate = $vatRate;
            if ($id) {
                $this->setID($id);
            } else {
                $this->createID();
            }
        }
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('vatRate', new Assert\Range(array(
            'min' => 0,
            'max' => 1,
            'minMessage' => 'vatRate must be at least {{ limit }}%.',
            'maxMessage' => 'vatRate must be at most {{ limit }}%.',
        )));
        $metadata->addPropertyConstraint('name', new Assert\NotBlank());
        $metadata->addPropertyConstraint('price', new Assert\NotBlank());
    }

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

    /**
     * @var int
     */
    private $price;

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $price int
     * @return int
     */
    public function setPrice($price)
    {
        return $this->price = $price;
    }

    /**
     * @var float
     */
    private $vatRate;

    /**
     * @return double
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }


    /**
     * @param $vatRate double
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;
    }

    /**
     * @return double
     */
    public function getPriceVat()
    {
        return $this->price * (1 + ($this->vatRate));
    }

    public static function createDbTable()
    {
        self::execute('
            CREATE TABLE IF NOT EXISTS product (
              name TEXT,
              price INTEGER,
              vatRate REAL,
              id INTEGER PRIMARY KEY
            )');
    }

}