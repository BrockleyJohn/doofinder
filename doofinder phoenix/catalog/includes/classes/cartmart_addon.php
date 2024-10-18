<?php
/**
 * @version 1.2.1
 * @variant PHOENIX
 - trait for behaviour across cartmart addons
 - checks whether there is an update available
 -- variant for phoenix

 * Version 1.2.1 catch json parse error and report version check fail
 * Version 1.2 add addonLink method
 * Version 1.1 remove jquery dependency
 * 
*/

trait cartmart_addon {

  protected static $addon_class_ver = '1.2.1';

  public static function addonLink()
  {
    return 'https://cartmart.uk/' . self::ADDON;
  }

  public static function debug() {
    return self::get_constant(self::CONFIG_KEY_BASE . 'DEBUG_LOG') == 'True';
  }

  public static function installed()
  {
    return (defined(static::CONFIG_KEY_BASE . 'STATUS'));
  }

  protected function addonCartmartCheck()
  {
    $return = false;
    if (defined('self::ADDON') && defined('self::VARIANT') && defined('self::VERSION')) {
      $query = [
        'variant' => self::VARIANT,
        'release' => self::VERSION,
        'action' => 'check',
      ];
      $response = $this->callCartmartAPI('addons/' . self::ADDON, $query, []);
      if ($response['httpcode'] == 200) {
        try {
          $data = json_decode($response['response'], true);
          if (isset($data['message']) && strlen($data['message'])) {
            $return = sprintf('<div class="%s alert" role="alert">%s</div>', $data['update'] ? 'alert-warning' : 'alert-info', $data['message']);
          }
        } catch (\Exception $e) {
          if (self::debug()) error_log('cartmart addon check failed: ' . $e->getMessage());
        }
      } else {
        if (self::debug()) error_log('cartmart addon check failed: ' . $response['httpcode']);
        $return = sprintf('<div class="alert-danger alert" role="alert">Version check failed for %s %s %s</div>', self::ADDON, self::VARIANT, self::VERSION);
      }
    } else {
      error_log('self::ADDON ' . (defined('self::ADDON') ? self::ADDON : 'not defined'));
      error_log('self::VARIANT ' . (defined('self::VARIANT') ? self::VARIANT : 'not defined'));
      error_log('self::VERSION ' . (defined('self::VERSION') ? self::VERSION : 'not defined'));
    }
    return $return;
  }

  public function addonCheckVersion()
  {
    if (basename($GLOBALS['PHP_SELF']) == 'modules.php' && $this->code == ($_GET['module'] ?? $this->code)) {
      if (! isset($_GET['action'])) {
        $this->description .= '<div id="cartmartaddon"></div>' . $this->onloadCheckScript();
      } elseif ($_GET['action'] == 'ajax' && isset($_GET['subaction']) && $_GET['subaction'] == 'addoncheck') {
        $message = $this->addonCartmartCheck();
        if ($message) {
          echo json_encode(['message' => $message]);
        }
        exit;
      }
    }
  }

  protected function callCartmartAPI($path, $query = null, $payload = [], $method = 'GET', $headers = [])
  {
    $url = 'https://cartmart.uk/api/' . $path;
    if (! is_null($query)) $url .= '?' . http_build_query($query);
    if (self::debug()) error_log('callCartmartAPI ' . $url);
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Accept: application/json';
    if (defined('CARTMART_ADDONS_API_KEY') && CARTMART_ADDONS_API_KEY != '') {
      try {
        $keys = json_decode(CARTMART_ADDONS_API_KEY, true);
        if (isset($keys[self::ADDON])) {
          $headers[] = 'X-Cartmart-Addons-Key: ' . $keys[self::ADDON];
        }
      } catch (\Exception $e) {

      }
    }
    $headers[] = 'X-Cartmart-Addons-Site: ' .  HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
    $headers[] = 'X-Cartmart-Addons-Ver: ' .  self::$addon_class_ver;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    if (! is_null($payload)) curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['httpcode' => $httpcode, 'response' => $response];
  }

  protected function onloadCheckScript()
  {
    $script = '<script>
    async function checkAddon() {
      const response = await fetch("' . Guarantor::ensure_global('Admin')->link('modules.php', ['set' => $_GET['set'], 'module' => $this->code, 'action' => 'ajax', 'subaction' => 'addoncheck'])->set_separator_encoding(false) . '");
      try {
        const data = await response.json();
        if (data.message) {
          document.getElementById("cartmartaddon").innerHTML = data.message;
        }
      } catch (e) {
        console.log("addon check error " + e);
      }
    }
    checkAddon();
  </script>';
    return $script;
  }
}