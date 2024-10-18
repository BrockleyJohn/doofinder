<?php
/**
  Simple Product XML Feed for CE Phoenix Cart
  Feed for Doofinder Search, Google Shopping etc

  This is a simple feed generator. If you want something fancy, get a paid addon!!

  author: @BrockleyJohn phoenix@cartmart.uk
  date: July 2024 
  copyright: SE Websites 2024
* released under MIT licence without warranty express or implied
*/
require 'includes/application_top.php';

$online = ('online' == ($_GET['mode'] ?? ''));

require language::map_to_translation('generate_products_feed.php');

$languages = language::load_all();

if (class_exists('cartmart_product_feed') && cartmart_product_feed::am_enabled() && sizeof($feed_language_currencies = cartmart_product_feed::get_enabled_feeds()) > 0) {

  // returns array of language_code => [currency_code, ...]

  if ($online) echo GENERATING_FEEDS . "<br>\n";

  try {

    foreach ($feed_language_currencies as $feed_lang => $feed_currencies) {

      $feeds = cartmart_product_feed::get_feeds($feed_lang, $feed_currencies);

      foreach ($feed_currencies as $currency) {

        $filename = $feeds->get_filename($currency);

        $fp = fopen(DIR_FS_CATALOG . $filename, 'w');
        if ($fp) {
          fwrite($fp, $feeds->get_xml($currency));
          fclose($fp);
          if ($online) echo sprintf(GENERATED, '<a href="' . $Linker->build($filename) . '" target="_blank">' . $filename . '</a>') . "<br>\n";
        } else {
          throw new Exception('Could not open ' . DIR_FS_CATALOG . $filename . ' for writing');
        }
  
      }

    }
  
  } catch (Exception $e) {
    error_log('Error generating product feed: ' . $e->getMessage());
    if ($online) echo sprintf(GENERATION_FAILED, ($filename ?? '')) . "<br>\n";
  }

}

require 'includes/application_bottom.php';
