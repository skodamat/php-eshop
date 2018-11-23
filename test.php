<?php

require 'vendor/autoload.php';

use Eshop\Model\Order;
use Eshop\Model\Product;
use Eshop\Model\UnregisteredCustomer;

use Eshop\Logging;
Logging::init();

use Eshop\Model\ActiveRecord;

ActiveRecord::setDb(new \PDO('sqlite:eshop.db'));

\Eshop\Model\Customer::createDbTable();
Product::createDbTable();
Order::createDbTable();

$c1 = new UnregisteredCustomer('Honza');
$c1->insert();
$c1->register();
$c1->update();

$p2 = new Product('hruška', 5, 0.1);
$p2->insert();

$o1 = new Order($c1, [$p2]);
$o1->doOrder();
$o1->insert();

$customers = \Eshop\Model\Customer::All();
$products = Product::All();


