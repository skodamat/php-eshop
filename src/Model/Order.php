<?php
namespace Eshop\Model;

/**
 * Class Order
 * @package Eshop\Model
 */
class Order extends ActiveRecord
{

    use UniqueId;

    /**
     * Order constructor.
     * @param null $customer Customer
     * @param array $items Product
     * @param $id int
     */
    public function __construct($customer = null, $items = [], $id = null)
    {
        if($customer)
        {
            $this->customer = $customer;
            $this->items = $items;

            if($id)
            {
                $this->setID($id);
            }
            else {
                $this->createID();
            }
        }

        $this->created = date("r");
    }

    /**
     * @var string
     */
    private $created;

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @var string
     */
    private $ordered;

    /**
     * @return string
     */
    public function getOrdered()
    {
        return $this->ordered;
    }

    /**
     * @param string $ordered
     * @return string
     */
    public function setOrdered($ordered)
    {
        return $this->ordered = $ordered;
    }

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param $customer Customer
     * @return Customer
     */
    public function setCustomer($customer)
    {
        return $this->customer = $customer;
    }

    /**
     * @var array Product
     */
    private $items;

    /**
     * @return array Product
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items Product
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @param $item Product
     */
    public function addItem($item)
    {
        array_push($this->items, $item);
    }

    /**
     * @param $product Product
     */
    public function removeItem($product){
        foreach ($this->items as $key => $i) {
            if ($i->getID() == $product->getID()) {
                unset($this->items[$key]);
            }
        }
    }

    public function getPrice() {
        $price = 0;
        foreach ($this->items as $i) {
            $price += $i->getPriceVat();
        }

        return $price;
    }

    public function doOrder() {
        $this->ordered = date("r");

        if( $this->customer instanceof RegisteredCustomer ){
            $this->customer->addLoyaltyPoints($this->getPrice() * RegisteredCustomer::LOYALTY_POINTS_COEF);
        }
    }

    protected static $relations = [
        'items' => [ self::HAS_MANY, Product::class ],
        'customer' => [ self::HAS_ONE, Customer::class ]
    ];

    public static function createDbTable()
    {
        self::execute('
            CREATE TABLE IF NOT EXISTS "order" (
              id INTEGER PRIMARY KEY,
              ordered TEXT,
              created TEXT,
              customer_id INTEGER
            )');

        self::execute('
            CREATE TABLE IF NOT EXISTS order_product (
              id INTEGER PRIMARY KEY,
              order_id INTEGER,
              product_id INTEGER 
            )
        ');
    }
}