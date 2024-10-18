<?php
/**
 * @version 0.1.0 
 * @variant PHOENIX 
   Simple logger addon
   - trait for inclusion in a module
   - handles addon not being present and uses error_log instead

   author John Ferguson @BrockleyJohn phoenix@cartmart.uk
   date June 2024
   copyright (c) 2024 SEwebsites
   released under SE Websites Commercial licence
* 
*/

trait cartmart_logger_addon {

  static $log_level = null;
  static $log_file = 'init';

  public static function get_level() {
    return self::$log_level;
  }

  public static function get_logfile() {
    if (self::$log_file == 'init') {
      self::$log_file = (! empty($setting = self::get_base_constant('LOG_FILE'))) ? $setting : null;
    }
    $check = cartmart_logger::check_logfile(self::$log_file);
    return $check['file'];
  }

  public static function log($message, $msg_level = 'INFO') {
    if (self::$log_file == 'init') {
      self::$log_file = (! empty($setting = self::get_base_constant('LOG_FILE'))) ? $setting : null;
    }
    if (null === self::$log_level) {
      if (! is_null(self::get_base_constant('LOG_LEVEL'))) {
        self::$log_level = self::get_base_constant('LOG_LEVEL');
      } elseif (method_exists(static::class, 'debug') && self::debug() || 'True' == self::get_base_constant('DEBUG')) {
        self::$log_level = 'DEBUG';
      } elseif (class_exists('cartmart_logger') && cartmart_logger::am_enabled()) {
        self::$log_level = cartmart_logger::get_log_level();
      } else {
        self::$log_level = 'ERROR';
      }
    }
    if (class_exists('cartmart_logger') && cartmart_logger::am_enabled()) {
      cartmart_logger::log($message, $msg_level, self::$log_file, self::$log_level);
    } elseif ($msg_level == 'ERROR' || self::$log_level != 'ERROR') {
      error_log($message);
    }
  }
  
  public static function log_debug($message) {
    self::log($message, 'DEBUG');
  }

  public static function log_error($message) {
    self::log($message, 'ERROR');
  }
  
  public static function log_info($message) {
    self::log($message, 'INFO');
  }

  public static function levels() {
    if (class_exists('cartmart_logger') && cartmart_logger::am_enabled()) {
      return cartmart_logger::levels();
    }
    return ['ERROR', 'INFO', 'DEBUG'];
  }

  public static function set_level($level) {
    self::$log_level = $level;
  }
  
}