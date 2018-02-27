<?php
/**
 * Created by PhpStorm.
 * User: ivanlesolart
 * Date: 2/18/18
 * Time: 11:39 AM
 */

require __DIR__ . "/vendor/autoload.php";

error_reporting(E_ALL);
ini_set('display_errors', '1');

setlocale(LC_TIME, "fr_FR");

$xmlDirPath = __DIR__ . '/xml/';

use Goutte\Client;
use Symfony\Component\Filesystem\Filesystem;



$localhost = 'http://localhost:9777';

// Create default dir

$fs = new Filesystem();
$resCreation = $fs->mkdir($xmlDirPath);

// Theatre de l'ile Agenda
/*
$crawler = $client->request('GET', 'https://theatredelile.nc/saison-2018');

// Get the latest post in this category and display the titles
$crawler->filter('#itemListPrimary > div.itemContainer')->each(function ($node) {
    //print $node->text()."\n";

    $node->filter('h3 a')->each(function ($nodeTitle) {
        echo $nodeTitle->text().'<br>';
        //$href = $nodeTitle->extract(array('href'));
        echo $nodeTitle->attr('href');
        //echo '<pre>'.$href[0].'</pre>';
        //exit();
    });
});
*/


// Noumea - Agenda
/*
$crawler = $client->request('GET', 'http://www.noumea.nc/agenda');

// Get the latest post in this category and display the titles
$crawler->filter('#listing > div:not(.node-unpublished)')->each(function ($node) {
    //print $node->text()."\n";

    $dateEvent = $node->filter('.cat_date > .date')->text();
    $lienEvent = $node->attr('id');
    $categorieEvent = $node->filter('.cat_date > .categorie')->text();
    echo $dateEvent. ' / '.$categorieEvent.' <a href="http://www.noumea.nc/node/'.explode('node-',$lienEvent)[1].'"">tt</a>';
    $node->filter('h3')->each(function ($nodeTitle) {
        echo $nodeTitle->text().'<br>';
        //$href = $nodeTitle->extract(array('href'));
        echo $nodeTitle->attr('href');
        //echo '<pre>'.$href[0].'</pre>';
        //exit();
    });
});
*/

// Centre d'art Agenda
// http://www.noumea.nc/centre-dart/agenda-centredart


// https://www.facebook.com/pg/galerieartebello.nc/events/?ref=page_internal
// => https://www.codeofaninja.com/demos/display-facebook-events-level-1/upcoming.php?fb_page_id=841152955896174

// https://www.facebook.com/lec.tic/events?lst=784979114%3A1207583158%3A1518931805
// https://www.facebook.com/pg/AndemicArtGallery/events/?ref=page_internal
// => https://www.codeofaninja.com/demos/display-facebook-events-level-1/upcoming.php?fb_page_id=225928980873207

// https://www.facebook.com/pg/nakamal21/events/?ref=page_internal
// => https://www.codeofaninja.com/demos/display-facebook-events-level-1/upcoming.php?fb_page_id=nakamal21

//https://www.codeofaninja.com/demos/display-facebook-events-level-1/upcoming.php?fb_page_id=290477564434561
// REX : https://www.facebook.com/pg/therex20152015/events/?ref=page_internal


function createXmlFile($pageName){
  return $pageName.'.xml';
}

function crawlbot($page = 'sortir'){

  $mainNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><agenda></agenda>');
  $productNode = $mainNode->addChild('eventList');
  
  $client = new Client();

  $crawler = $client->request('GET', $localhost.'/'.$page.'.html');

  echo '<pre>Données</pre>';

  // Get data from the page
  $crawler->filter('#main_container .wpb_content_element div.events_holder')->each(function ($node) use (&$productNode){
      $node->filter('h2')->each(function ($nodeTitle) use (&$productNode){
          $rN = $productNode->addChild('evenement');
          $rN->addChild('titleEvent', $nodeTitle->text());
          $rN->addChild('sourceLink', $nodeTitle->attr('href'));
      });
  });

  $fs = new Filesystem();
  $fileName = createXmlFile($page);
  $mainNode->asXML($xmlfilePath . $fileName);
}


dataCentreDart($localhost, 'centredart', $xmlDirPath);

function dataCentreDart($localhost, $page = 'centredart', $xmlDirPath){
  $client = new Client();
  
  $xmlNode = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><agenda></agenda>');
  $productNode = $xmlNode->addChild('eventList');
  
  $crawler = $client->request('GET', $localhost.'/agenda-centredart.html');

  // Get the latest post in this category and display the titles
  $crawler->filter('#listing > div:not(.node-unpublished)')->each(function ($node) use (&$productNode) {
      
      $rN = $productNode->addChild('evenement');
      $rN->addChild('title', $node->filter('h3')->text());
      
      //Date de l'évenement
      $dateEvent = $node->filter('.cat_date > .date')->text();
      $dateExplode = explode('/',$dateEvent);
      
      if(is_array($dateExplode) && count($dateExplode) > 1){
        $dateEventStart = convertDate(trim($dateExplode[0]));
        $dateEventEnd = convertDate(trim($dateExplode[1]));
      }else{
        $dateEventStart = convertDate($dateEvent);
      }
      
      $rN->addChild('dateStart', $dateEventStart);
      if(isset($dateEventEnd)){
        $rN->addChild('dateEnd', $dateEventEnd);
      }
      
      $rN->addChild('morelink', "http://www.noumea.nc/node/".explode('node-',$node->attr('id'))[1]);
      $rN->addChild('sourceLink', $node->filter('h3')->attr('href'));

      //$categorieEvent = $node->filter('.cat_date > .categorie')->text();
     /*
      $node->filter('h3')->each(function ($nodeTitle) {
          echo $nodeTitle->text().'<br>';
          echo $nodeTitle->attr('href');
    
      });*/
  });
  
  $fs = new Filesystem();
  $fileName = createXmlFile($page);
  $xmlNode->asXML($xmlDirPath . $fileName);
}

function convertDate($inputDate){
  //echo strtotime($inputDate);
  setlocale(LC_TIME, 'fr_FR');
  if(isset($inputDate))
    return strftime("%d/%m/%Y",strtotime($inputDate));
}