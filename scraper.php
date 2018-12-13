<?php
require 'vendor/autoload.php';

use Goutte\Client;
use Eshop\Model\Product;

use Eshop\Logging;
Logging::init();

use Eshop\Model\ActiveRecord;

ActiveRecord::setDb(new \PDO('sqlite:eshop.db'));

function scrapeBooks ()
{
    $client = new Client();

    $books = [];

    $crawler = $client->request('GET', 'https://www.kosmas.cz/kategorie/64/?Filters.ArticleTypeIds=3563&name=detektivky');

    $crawler->filter('.g-item__title')->each(function ($node, $i) use (&$books) {
        $books[$i] = ['title' => trim($node->text())];
    });
    $crawler->filter('.g-item__authors')->each(function ($node, $i) use (&$books){
        $books[$i]['author'] = trim($node->text());
    });
    $crawler->filter('.price__default')->each(function ($node, $i) use (&$books){
        $books[$i]['price'] = trim($node->text());
    });

    foreach ($books as $book) {
        $newBook = new Product($book['title'] . ' - ' . $book['author'], $book['price'], 0);
        print_r($newBook);
//        $newBook->insert();
    }
}

//scrapeBooks();

function scrapeTickets () {
    $client = new Client(['timeout'=>60]);

    $tickets = [];

    $crawler = $client->request('GET','https://www.ticketportal.cz/#type=2');

    $crawler->filter('.event-name')->each(function ($node,$i) use (&$tickets){
        $tickets[$i]['event'] = trim( $node->text() );
    });

    $crawler->filter('.event-date')->each(function ($node,$i) use (&$tickets){
        $tickets[$i]['date'] = trim( $node->text() );
        print $node->text();
    });

    $crawler->filter('.event-venue-info')->each(function ($node,$i) use (&$tickets){
        $tickets[$i]['place'] = trim( $node->text() );
    });

    print_r($tickets);
}

scrapeTickets();