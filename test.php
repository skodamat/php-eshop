<?php

require 'vendor/autoload.php';
require 'scraper.php';

use Eshop\Model\Order;
use Eshop\Model\Product;

use Eshop\Logging;
Logging::init();

use Eshop\Model\ActiveRecord;

ActiveRecord::setDb(new \PDO('sqlite:eshop.db'));

\Eshop\Model\Customer::createDbTable();
Product::createDbTable();
Order::createDbTable();



