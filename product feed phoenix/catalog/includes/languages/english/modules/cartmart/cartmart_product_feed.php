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

$pfx = 'MODULE_CARTMART_PRODUCT_FEED_';

$defs = [

  $pfx . 'TEXT_TITLE' => 'Simple Product Feed',
  $pfx . 'TEXT_DESCRIPTION' => 'Controls generation of a simple product XML feed for Doofinder Search, Google Shopping etc',

  // admin validation
  $pfx . 'ERROR_OP_LOC_NOT_WITHIN_SITE' => '"%s" contains dots. Use a location within the site',
  $pfx . 'ERROR_OP_LOC_NOT_DIR' => '"%s "is not a directory',
  $pfx . 'ERROR_OP_LOC_NOT_WRITABLE' => '"%s" is not writable',
  $pfx . 'OUTPUT_LOCATION_OK' => '"%s" is writable',

  // admin config texts
  $pfx . 'STATUS' . '_TITLE' => 'Module Status',
  $pfx . 'STATUS' . '_DESC' => 'Do you want to enable feed generation?',
  $pfx . 'WEIGHT_UNIT' . '_TITLE' => 'Weight Unit',
  $pfx . 'WEIGHT_UNIT' . '_DESC' => 'Unit of measurement of product weights',
  $pfx . 'ENABLE_FEEDS' . '_TITLE' => 'Enable Feeds',
  $pfx . 'ENABLE_FEEDS' . '_DESC' => 'Select combinations of language and currency for which to generate',
  $pfx . 'OUTPUT_LOCATION' . '_TITLE' => 'Output Location',
  $pfx . 'OUTPUT_LOCATION' . '_DESC' => 'Where should the feed be saved? Leave blank to output to the shop root.',
  $pfx . 'FILE_STUB' . '_TITLE' => 'File Stub',
  $pfx . 'FILE_STUB' . '_DESC' => 'The start of the filename for the feed. The language code will be appended to this.',

];

foreach ($defs as $key => $val) {
  if (!defined($key)) {
    define($key, $val);
  }
}

