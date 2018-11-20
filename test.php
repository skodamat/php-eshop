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

$c1 = new UnregisteredCustomer('Honza', 2);
$c1->insert();
$c1 = $c1->register();
$c1->insert();

//use Symfony\Component\Validator\Validation;


//$validator = Validation::createValidatorBuilder()
//    ->addMethodMapping('loadValidatorMetadata')
//    ->getValidator();

//$c1 = new UnregisteredCustomer('Honza');
//$c1->insert();

//$errors = $validator->validate($product1);

//if( count($errors) > 0 ){
//    foreach ($errors as $err) {
//        Logging::error($err);
//    }
//}
