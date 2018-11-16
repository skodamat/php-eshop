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


//use Symfony\Component\Validator\Validation;


//$validator = Validation::createValidatorBuilder()
//    ->addMethodMapping('loadValidatorMetadata')
//    ->getValidator();

$p1 = new Product('televize', 10000, 0.22, 1);
$p1->insert();

$c1 = new UnregisteredCustomer('Pepa');
$c1->insert();

//$errors = $validator->validate($product1);

//if( count($errors) > 0 ){
//    foreach ($errors as $err) {
//        Logging::error($err);
//    }
//}
