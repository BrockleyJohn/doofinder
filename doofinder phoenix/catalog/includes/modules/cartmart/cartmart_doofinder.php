<?php
/**
  Doofinder addon for CE Phoenix Cart
  add smart search to your store
  author: @BrockleyJohn phoenix@cartmart.uk
  date: July 2024 
  copyright: SE Websites 2024
* released under MIT licence without warranty express or implied
*/

class cartmart_doofinder extends abstract_module {

  use cartmart_addon, cartmart_cfg_helper;

  const ADDON = 'DOOFINDER';
  const VARIANT = 'PHOENIX';
  const VERSION = '0.1.0';

  const CONFIG_KEY_BASE = 'MODULE_CARTMART_DOOFINDER_';

  public static function script_check($value)
  {
    $check = self::script_validate($value);
    if ($check['ok']) {
      return '<i class="fas fa-check-circle text-success"></i> ' . self::get_base_constant('SCRIPT_OK');
    } else {
      return '<i class="fas fa-times-circle text-danger"></i> ' . $check['message'];
    }
  }

  protected static function script_validate($script)
  {
    if (empty($script)) {
      return [
        'ok' => false,
        'message' => self::get_base_constant('ERROR_SCRIPT_EMPTY'),
      ];
    }
    if (stripos($script, '<script') === false) {
      return [
        'ok' => false,
        'message' => self::get_base_constant('ERROR_NO_SCRIPT_TAGS'),
      ];
    }
    $url = str_ireplace(['<script src="', '" async></script>'], '', $script);
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
      return [
        'ok' => false,
        'message' => sprintf(self::get_base_constant('ERROR_SCRIPT_URL'), $url),
      ];
    }
    return ['ok' => true];
  }

  public function __construct()
  {
    parent::__construct();
    if (self::script_validate($this->base_constant('DOOF_SCRIPT'))['ok'] == false) {
      $this->description .= '<div class="alert alert-danger">' . $this->base_constant('NO_LOAD_WITHOUT_SCRIPT') . '</div>';
    }
  }

  protected function get_parameters()
  {
    return [
      static::CONFIG_KEY_BASE . 'STATUS' => [
        'title' => self::cfg_title('STATUS'),
        'desc' => self::cfg_desc('STATUS'),
        'value' => 'True',
        'set_func' => "Config::select_one(['True', 'False'], ",
      ],
      static::CONFIG_KEY_BASE . 'DOOF_SCRIPT' => [
        'title' => self::cfg_title('DOOF_SCRIPT'),
        'desc' => self::cfg_desc('DOOF_SCRIPT'),
        'value' => '',
        'use_func' => "cartmart_doofinder::script_check",
      ],
      static::CONFIG_KEY_BASE . 'SPIDER_SUPPRESS' => [
        'title' => self::cfg_title('SPIDER_SUPPRESS'),
        'desc' => self::cfg_desc('SPIDER_SUPPRESS'),
        'value' => 'True',
        'set_func' => "Config::select_one(['True', 'False'], ",
      ],
    ];
  }
}