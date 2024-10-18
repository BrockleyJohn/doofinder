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

class cartmart_product_feed extends abstract_module {

  use cartmart_addon, cartmart_cfg_helper;

  const CONFIG_KEY_BASE = 'MODULE_CARTMART_PRODUCT_FEED_';

  public static function check_output_loc($value) {
    $check = trim($value, '/');
    $error = false;
    if (strpos($check, '..') !== false) {
      $error = sprintf(self::get_base_constant('ERROR_OP_LOC_NOT_WITHIN_SITE'), $check);
    } else {
      if (! file_exists(DIR_FS_CATALOG . $check)) {
        mkdir(DIR_FS_CATALOG . $check, 0755, true);
      }
      if (! is_dir(DIR_FS_CATALOG . $check)) {
        $error = sprintf(self::get_base_constant('ERROR_OP_LOC_NOT_DIR'), $check);
      } 
    }
    if ($error) {
      return '<i class="fas fa-times-circle text-danger"></i> ' . $error;
    }
    return '<i class="fas fa-check-circle text-success"></i> ' . sprintf(self::get_base_constant('OUTPUT_LOCATION_OK'), $check);
  }

  public static function feeds_all() {
    $return = [];
    $languages = language::load_all();
    Guarantor::ensure_global('currencies');
    foreach (array_keys($languages) as $language) {
      foreach (array_keys($GLOBALS['currencies']->currencies) as $currency) {
        $return[] = $language . '|' . $currency;
      }
    }
    return $return;
  }

  public static function get_enabled_feeds() {
    $feeds = [];
    $cfg_feeds = explode(';', self::get_base_constant('ENABLE_FEEDS'));
    foreach ($cfg_feeds as $cfg_feed) {
      list($language, $currency) = explode('|', $cfg_feed);
      $feeds[$language][] = $currency;
    }
    return $feeds;
  }

  public static function get_feeds($language_code, $currency_codes) {
    $full_file_stub = trim(self::get_base_constant('OUTPUT_LOCATION'), '/') . '/' . self::get_base_constant('FILE_STUB');
    return new cartmart_feed_builder($language_code, $currency_codes, $full_file_stub);
  }

  public function __construct() {
    parent::__construct();

    // add button for feed file names
    // add button for feed generation
  }

  public static function get_weight_unit() {
    return self::get_base_constant('WEIGHT_UNIT');
  }

  protected function get_parameters() {
    return [
      static::CONFIG_KEY_BASE . 'STATUS' => [
        'title' => self::cfg_title('STATUS'),
        'desc' => self::cfg_desc('STATUS'),
        'value' => 'True',
        'set_func' => "Config::select_one(['True', 'False'], ",
      ],
      static::CONFIG_KEY_BASE . 'WEIGHT_UNIT' => [
        'title' => self::cfg_title('WEIGHT_UNIT'),
        'desc' => self::cfg_desc('WEIGHT_UNIT'),
        'value' => 'kg',
        'set_func' => "Config::select_one(['lb', 'oz', 'g', 'kg'], ",
      ],
      static::CONFIG_KEY_BASE . 'ENABLE_FEEDS' => [
        'title' => self::cfg_title('ENABLE_FEEDS'),
        'desc' => self::cfg_desc('ENABLE_FEEDS'),
        'value' => '',
        'use_func' => 'cartmart_product_feed::cfg_show_unpacked',
        'set_func' => "Config::select_multiple(cartmart_product_feed::feeds_all()" . ", ",
      ],
      static::CONFIG_KEY_BASE . 'OUTPUT_LOCATION' => [
        'title' => self::cfg_title('OUTPUT_LOCATION'),
        'desc' => self::cfg_desc('OUTPUT_LOCATION'),
        'value' => '',
        'use_func' => 'cartmart_product_feed::check_output_loc',
      ],
      static::CONFIG_KEY_BASE . 'FILE_STUB' => [
        'title' => self::cfg_title('FILE_STUB'),
        'desc' => self::cfg_desc('FILE_STUB'),
        'value' => 'product-feed',
      ],
    ];
  }

}