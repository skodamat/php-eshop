<?php

namespace Eshop\Test\Model;

use Eshop\Model\ActiveRecord;
use Eshop\Model\Customer;
use PHPUnit\Framework\TestCase;
use Eshop\Logging as Logger;
Logger::init();

class CustomerTest extends TestCase
{
    /** @var \PDO */
    protected static $pdo;

    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite:test.db');

        ActiveRecord::setDb(self::$pdo);
        self::$pdo->query('DROP TABLE customer');
        Customer::createDbTable();
    }

    public function testProperties()
    {
        $customer = new Customer('Bedrich', 2);

        $this->assertEquals(2, $customer->getId());
        $this->assertEquals('Bedrich', $customer->getName());

        $customer->setName('Bohuslav');
        $this->assertEquals('Bohuslav', $customer->getName());

        return $customer;
    }

    /** @depends testProperties */
    public function testInsert(Customer $customer)
    {
        $customer->insert();

        $statement = self::$pdo->prepare('SELECT id, name FROM customer WHERE id=:id');

        $this->assertNotFalse($statement);

        $statement->execute(['id' => 2]);

        $this->assertNotFalse($statement);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(2, $result['id']);

        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('Bohuslav', $result['name']);
    }

    /** @depends testInsert */
    public function testFind()
    {
        $customer = Customer::find(2);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertAttributeEquals(2, 'id', $customer);
        $this->assertAttributeEquals('Bohuslav', 'name', $customer);
    }
}
