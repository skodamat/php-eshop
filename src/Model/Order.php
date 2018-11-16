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
     * @param null $customer object
     * @param null $items array
     */
    public function __construct($customer = null, $items = null, $id = null)
    {
        if ($id){
            $this->id = $id;
        }else {
            $this->createID();
        }
        $this->created = date("r");
        $this->customer = $customer;
        $this->items = $items;
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

    private $customer;

    /**
     * @return object
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param $customer object
     * @return object
     */
    public function setCustomer($customer)
    {
        return $this->customer = $customer;
    }

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
        $this->items[] = $item;
    }

    /**
     * @param $id Product
     */
    public function removeItem($id){
        foreach ($this->items as $key => $i) {
            if ($i->getID() == $id) {
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
        'items' => [ self::HAS_MANY, Product::class ]
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