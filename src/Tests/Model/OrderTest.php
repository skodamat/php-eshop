<?php

namespace Eshop\Test\Model;

use Eshop\Model\ActiveRecord;
use Eshop\Model\Customer;
use Eshop\Model\Order;
use Eshop\Model\Product;
use PHPUnit\Framework\TestCase;
use Eshop\Logging as Logger;
Logger::init();

/**
 * Class OrderTest
 * @package Eshop\Test\Model
 */
class OrderTest extends TestCase
{
    /** @var \PDO */
    protected static $pdo;

    /**
     *
     */
    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite:test.db');

        ActiveRecord::setDb(self::$pdo);

        self::$pdo->query('DROP TABLE order');
        self::$pdo->query('DROP TABLE product');
        self::$pdo->query('DROP TABLE customer');

        Order::createDbTable();
        Product::createDbTable();
        Customer::createDbTable();
    }

    /**
     * @return array
     */
    public function testProperties ()
    {
        $product = new Product('hruška', 5, 0.1, 1);
        $customer = new Customer('Láďa', 1);
        $order = new Order($customer, [], 1);

        $order->addItem($product);
        $this->assertContains($product, $order->getItems());

        $order->removeItem($product);
        $this->assertNotContains($product, $order->getItems());

        $order->addItem($product);

        return ['product' => $product, 'customer' => $customer, 'order' => $order ];
    }

    /**
     * @param array $objects
     * @depends testProperties
     */
    public function testInsert ( $objects )
    {
        $objects['product']->insert();
        $objects['customer']->insert();
        $objects['order']->insert();

        $statement = self::$pdo->prepare('SELECT id, customer_id FROM "order" WHERE id=:id');
        $this->assertNotFalse($statement);

        $statement->execute([ 'id' => 1 ]);
        $this->assertNotFalse($statement);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(1, $result['id']);

        $this->assertArrayHasKey('customer_id', $result);
        $this->assertEquals('1', $result['customer_id']);
    }

    /** @depends testInsert */
    public function testFind()
    {
        $order = Order::find(1);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertAttributeEquals(1, 'id', $order);
        $this->assertAttributeInstanceOf(Customer::class, 'customer', $order);
        $this->assertAttributeContainsOnly(Product::class, 'items', $order);
    }
}