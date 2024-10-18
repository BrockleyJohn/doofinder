<?php
/**
 * @version 1.1
 * @variant PHOENIX
 - adds cartmart modules to module types
 - author @BrockleyJohn phoenix@cartmart.uk

 * Version 1.1 July 2024 Phoenix 1.0.9.3 compatibility
 * Released under the GNU General Public License
*/

  class cfgm_cartmart {

    const CODE = 'cartmart';
    const DIRECTORY = DIR_FS_CATALOG . 'includes/modules/cartmart/';
    const LANGUAGE_DIRECTORY = DIR_FS_CATALOG . 'includes/languages/';
    const KEY = 'MODULE_CARTMART_INSTALLED';
    const TITLE = MODULE_CFG_MODULE_CARTMART_TITLE;
    const TEMPLATE_INTEGRATION = false;
    const GET_HELP_LINK = 'https://cartmart.uk/info.php?pages_id=5';
    const GET_ADDONS_LINKS = [CARTMART_ADDONS_LINK => 'https://cartmart.uk/',];

    function __construct() {
      $this->code = static::CODE;
      $this->directory = static::DIRECTORY;
      $this->language_directory = static::LANGUAGE_DIRECTORY;
      $this->key = static::KEY;
      $this->title = static::TITLE;
      $this->template_integration = false;
    }

  }
