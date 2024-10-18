<?php
/**
 * @version 1.4.2 
 * @variant PHOENIX 
  reuse methods for configuration settings across core module hierarchies
  more on: cartmart.uk
  v 1.4.2 add cfg_update_function
  v 1.4.1 add version variant to docblock
  version: v1.4  add multi-check select (reusable but for shipping modules)
  version: v1.3
  author: John Ferguson @BrockleyJohn oscommerce@sewebsites.net
  date: Feb 2021
  copyright (c) SEwebsites 2021
  released under MIT licence without warranty express or implied
  ** may be included in addons with a more restrictive licence **
*/

trait cartmart_cfg_helper {

  protected static function base_name($suffix)
  {
    return self::CONFIG_KEY_BASE . $suffix;
  }
  
  protected static function get_base_constant($suffix) 
  {
    return defined(self::CONFIG_KEY_BASE . $suffix) ? constant(self::CONFIG_KEY_BASE . $suffix) : null;
  }

  public static function cfg_title($suffix)
  {
    if (! defined(static::CONFIG_KEY_BASE . $suffix . '_TITLE')) {
      exit("'Is language file missing? No definition for '$suffix'");
    }
    return constant(static::CONFIG_KEY_BASE . $suffix . '_TITLE');
  }
  
  public static function cfg_desc($suffix)
  {
    return constant(static::CONFIG_KEY_BASE . $suffix . '_DESC');
  }

  protected static function get_code()
  {
    $code = get_called_class();
    return str_replace('\\', '', $code);
  }

  public static function am_enabled() 
  {
    return self::get_base_constant('STATUS') == 'True';
  }

  public static function cfg_net_gross_price($val)
  {
    $val = (float)$val;
    $tax = Tax::get_rate(self::get_base_constant('TAX_CLASS'));
    return sprintf(self::get_base_constant('CFG_NET_GROSS'), $val, Tax::price($val, $tax));
    // return 'cfg_net_gross_price not yet implemented';
  }

  public static function onPage($pagevar)
  {
    $pages = self::unpack_var($pagevar);
    return in_array($GLOBALS['PHP_SELF'], $pages);
  }
  
  protected static function rates_field_as_table($val, $field, $to_col, $net, $tax, $gross, $up_to, $tgt = null) {
    static $i = 0;
    $field_id = 'rateField' . $i;
    $target = (! Text::is_empty($tgt) ? " class=\"modaltext\" data-fieldtgt=\"{$tgt}\"" : '');
    $output = tep_draw_hidden_field($field, $val, "id=\"{$field_id}\"{$target}");
    $vals = self::unpack_var($val);
    // echo($tax . '<pre>' . print_r($vals,true) . '</pre>');
    if (count($vals)) {
      $output .= <<<EOD
      <div class="table-responsive">
        <table class="table table-striped table-hover" id="rateTable{$i}" data-fieldid="$field_id">
          <thead class="thead-dark">
            <tr>
              <th></th>
              <th class="text-right">{$to_col}</th>
              <th class="text-right">{$net}</th>
              <th class="text-right">{$gross}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
EOD;
      foreach ($vals as $r => $row) {
        $price = $row[1] ?? '';
        $output .= '<tr id="row' . $r . '" class="data"><td class="text-right align-middle">' . $up_to . '</td><td class="text-right align-middle">' . tep_draw_input_field('limit' . $r, $row[0] ?? '', "id=\"limit{$r}\"", 'text', true, 'class="form-control text-right" data-table="rateTable' . $i . '" onChange="tablechange(event)"') . 
          '</td><td class="text-right align-middle">' . tep_draw_input_field('price' . $r, $row[1] ?? '', "id=\"price{$r}\"", 'text', true, 'class="form-control text-right price" data-grossfield="gross' . $r . '" data-table="rateTable' . $i . '" onChange="pricechange(event)"') . 
          '</td><td class="text-right align-middle">' . tep_draw_input_field('gross' . $r, (is_array($row) && array_key_exists(1, $row) ? tep_add_tax($row[1], $tax, true) : ''), "id=\"gross{$r}\"", 'text', true, 'class="form-control text-right gross" data-pricefield="price' . $r . '" data-table="rateTable' . $i . '" onChange="grosschange(event)"') . 
          '</td><td class="align-middle">' . '<a href="#" class="delRow" data-row="' . $r . '" data-tableid="rateTable' . $i . '" onClick="delrow(event)" title="' . IMAGE_DELETE . '"><i class="fas fa-minus-circle fa-2x text-danger" data-row="' . $r . '" data-tableid="rateTable' . $i . '"></i>' . "</td></tr>\n";
      }
      $output .= '<tr id="insertRow' . $i . '"><td colspan="5" class="align-middle text-right">' . '<a href="#" class="addRow" data-lastrow="' . $r . '" data-tableid="rateTable' . $i . '" onClick="addrow(event)" title="' . IMAGE_INSERT . '"><i class="fas fa-plus-circle fa-2x text-primary addRow" data-lastrow="' . $r . '" data-tableid="rateTable' . $i . '"></i>' . 
      "</td></tr></tbody></table></div>\n";
    }
    
    $i++;
    return $output;
  }
  
  protected static function rates_table_scripts($tax)
  {
    
    $GLOBALS['footer_scripts'][] = <<<EOD
<script>
  function grosschange(e) {
    var g = e.target.value;
    const pid = e.target.dataset.pricefield;
    g = g * 100/(100 + {$tax});
    var pfield = document.querySelector('#'+pid);
    pfield.value = g;
    tablechange(e);
  }

  function pricechange(e) {
    var p = e.target.value;
    const gid = e.target.dataset.grossfield;
    p = p * (100 + {$tax})/100;
    var gfield = document.querySelector('#'+gid);
    gfield.value = p;
    tablechange(e);
  }
  
  function tablechange(e) {
    var newval = '';
    var l, p;
    const tableid = e.target.dataset.table;
    var table = document.querySelector('#'+tableid);
    for (var i = 0; i < table.rows.length; i++) {
      if (table.rows[i].classList.contains('data')) {
        l = table.rows[i].querySelector('input[name^="limit"]');
        p = table.rows[i].querySelector('input[name^="price"]');
        newval += l.value + ':' + p.value + ',';
      }
    }
    if (i > 0) newval = newval.slice(0, -1);
    var ip = document.querySelector('#'+table.dataset.fieldid);
    ip.value = newval;
  }
  
  function addrow(e) {
    var tableid = e.target.dataset.tableid;
    // get the last row number, add 1 and resave
    var lastrow = parseInt(e.target.dataset.lastrow) + 1;
    //e.target.dataset.lastrow = lastrow;
    updateLastRow(tableid, lastrow);
    // insert the row
    var tRef = document.getElementById(tableid);
    tRef.insertRow(lastrow + 1).innerHTML = 
    '<td class="text-right align-middle"></td><td class="text-right align-middle"><input type="text" name="limit' + lastrow + '" value="" id="limit' + lastrow + '" class="form-control text-right" data-table="rateTable" onchange="tablechange(event)"></td><td class="text-right align-middle"><input type="text" name="price' + lastrow + '" value="" id="price' + lastrow + '" class="form-control text-right price" data-grossfield="gross' + lastrow + '" data-table="rateTable" onchange="pricechange(event)"></td><td class="text-right align-middle"><input type="text" name="gross' + lastrow + '" value="" id="gross' + lastrow + '" class="form-control text-right gross" data-pricefield="price' + lastrow + '" data-tableid="' + tableid + '" onchange="grosschange(event)"></td><td class="align-middle"><a href="#" class="delRow" data-row="' + lastrow + '" data-tableid="' + tableid + '" onclick="delrow(event)" title="Delete"><i class="fas fa-minus-circle fa-2x text-danger" data-row="' + lastrow + '" data-tableid="' + tableid + '"></i></a></td>';
    e.preventDefault();
    e.stopPropagation();
  }
  
  function updateLastRow(tableid, rowno) {
    var addlink = document.querySelector('#' + tableid + ' a.addRow');
    addlink.dataset.lastrow = rowno;
    var addIcon = document.querySelector('#' + tableid + ' i.addRow');
    addIcon.dataset.lastrow = rowno;
  }
  
  function delrow(e) {
    var tableid = e.target.dataset.tableid;
    var rowno = parseInt(e.target.dataset.row);
    var tRef = document.getElementById(tableid);
    tRef.deleteRow(rowno);
    var addlink = document.querySelector('#' + tableid + ' a.addRow');
    var lastrow = parseInt(addlink.dataset.lastrow) - 1;
    updateLastRow(tableid, lastrow);
    e.preventDefault();
    e.stopPropagation();
  }
</script>
EOD;
  }

  public static function cfg_show_unpacked($text, $separator = ';') {
    return nl2br(implode("\n", explode($separator, $text)));
  }

  protected static function cfg_update_function($fn, $from, $to)
  {
    $cq = $GLOBALS['db']->query(sprintf('SELECT configuration_id FROM `configuration` WHERE configuration_key LIKE "%s" AND %s = "%s"', self::CONFIG_KEY_BASE . '%', $fn, $from));
    while ($cq->fetch_assoc()) {
      $GLOBALS['db']->perform('configuration', [$fn => $to], 'update', 'configuration_id = "' . $row['configuration_id'] . '"');
    }
  }
  
  protected static function unpack_var($val, $i = 0)
  {
    $cs = [',', ':', '!'];
    $out = [];
    $work = explode($cs[$i], $val);
    foreach ($work as $bit) {
      if (isset($cs[$i + 1]) && strpos($bit, $cs[$i + 1])) {
        $out[] = self::unpack_var($bit, $i + 1);
      } else {
        $out[] = $bit;
      }
    }
    return $out;
  }
  
  protected static function unsep($val, $sep = ',') {
    $out = [];
    $work = explode($sep, $val);
    foreach ($work as $bit) {
      if (strlen(trim($bit))) {
        $out[] = trim($bit);
      }
    }
    return $out;
  }

  protected static function cfg_edit_name($key)
  {
    return 'configuration[' . $key . ']';
  }
  
  public static function hide($val, $key)
  // hide the edit field so can set it from script  
  {
    $name = Text::is_empty($key) ? 'configuration_value' : 'configuration[' . $key . ']';
    return new Input($name, ['value' => $val], 'hidden');
    //return tep_draw_hidden_field($name, $val);
  }
  
  public static function nowt() { /* suppress edit boxes altogether */ }

  protected static function language_file()
  // when using cartmart module statically need to include language when needed
  {
    return language::map_to_translation('/modules/cartmart/' . __CLASS__ . '.php');
  }

  public static function cfg_category_drop_down($val, $key)
  {
    $name = Text::is_empty($key) ? 'configuration_value' : 'configuration[' . $key . ']';
    return tep_draw_pull_down_menu($name, tep_get_category_tree(), (int)$val);
  }
  
  public static function cfg_payment_module_drop_down()
  {
    return self::cfg_module_drop_down('payment');
  }
  
  public static function cfg_shipping_module_multicheck($chk_all, $val, $key)
  {
    return self::cfg_module_multicheck('shipping', $val, $key, ($chk_all == 'all'));
  }

  protected static function cfg_module_multicheck($set, $val, $key, $all)
  {
    $modules = self::cfg_get_installed_modules($set);
    $poss = [];
    foreach ($modules as $m) {
      $poss[$m['code']] = $m['title'];
    }
    return self::cfg_multicheck($poss, self::unsep($val), $key, $all);
  }

  protected static function cfg_multicheck($possibles, $current, $key, $all = false)
  {
    $r = '';
    if (count($possibles)) {
      foreach ($possibles as $v => $d) {
        $r .= "<br/>\n" . tep_draw_checkbox_field($key . '_sgl[]', $v, in_array($v, $current)) . '&nbsp;' . Text::output($d);
      }
      if ($all) {
        $r .= "<br/><br/>\n" . tep_draw_checkbox_field($key . '_all') . '&nbsp;' . TEXT_ALL;
      }
      $r .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="' . $key . '_cfg"');

      // I am not convinced we will ever display more than one on a page but just in case make unique
      $r .= <<<EOS
  <script>
    function {$key}_cfg_upd() {
      let vals = [];

      let chks = document.querySelectorAll('input[name="{$key}_sgl[]"]:checked');

      for (var chk of chks) {
        vals.push(chk.value);
      }

      document.getElementById('{$key}_cfg').value = vals.join();
    }

    {$key}_cfg_upd();

    let {$key}_flds = document.querySelectorAll('input[name^="{$key}_sgl"]');

    Array.from({$key}_flds).forEach(fld => {
      fld.addEventListener('change', function() {
        {$key}_cfg_upd();
      }); 
    });

    const {$key}_all = document.querySelector('input[name="{$key}_all"]');

    if (null !== {$key}_all) {
      {$key}_all.addEventListener('click', function() {
        for (var fld of {$key}_flds) {
          fld.checked = this.checked;
        }
        {$key}_cfg_upd();
      });
    }

  </script>
EOS;
    } else {
      $r = TEXT_NONE;
    }
    return $r;
  }
  
  public static function cfg_shipping_module_drop_down()
  {
    return self::cfg_module_drop_down('shipping');
  }
  
  protected static function cfg_module_drop_down($set)
  {
    $modules = self::cfg_get_installed_modules($set);
    $l = [];
    foreach ($modules as $m) {
      $l[] = [
        'id' => $m['code'],
        'text' => $m['title'],
      ];
    }
    return $l;
  }
  
  protected static function cfg_get_installed_modules($set, $language = null)
  {
    $return = [];
    if (is_null($language)) {
      $language = $GLOBALS['language'];
    }
    $modules = $GLOBALS['cfgModules']->getAll();
    $module_type = $GLOBALS['cfgModules']->get($set, 'code');
    $module_directory = $GLOBALS['cfgModules']->get($set, 'directory');
    $module_language_directory = $GLOBALS['cfgModules']->get($set, 'language_directory');
    $module_key = $GLOBALS['cfgModules']->get($set, 'key');;
    $modules_installed = (defined($module_key) ? explode(';', constant($module_key)) : array());

    $file_extension = '.php';
    $directory_array = array();
    if ($dir = @dir($module_directory)) {
      while ($file = $dir->read()) {
        if (!is_dir($module_directory . $file)) {
          if (substr($file, strrpos($file, '.')) == $file_extension) {
            if (in_array($file, $modules_installed)) {
              $directory_array[] = $file;
            }
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
      $file = $directory_array[$i];

      include_once($module_language_directory . $language . '/modules/' . $module_type . '/' . $file);
      include_once($module_directory . $file);

      $class = substr($file, 0, strrpos($file, '.'));
      if (class_exists($class)) {
        $module = new $class;
        if ($module->check() > 0) { // is it really installed?

          $module_info = [
            'code' => $module->code,
            'title' => $module->title,
            'status' => ($module->enabled ? 1 : 0),
            'public' => (isset($module->public_title) ? $module->public_title : $module->title)
          ];

          $return[] = $module_info;
        }
      }
    }
    return $return;
  }

  protected static function setvar ($key, $val)
  {
    $GLOBALS['db']->query(sprintf('UPDATE `configuration` SET configuration_value = \'%s\' WHERE configuration_key = \'%s\'', $val, $key));
  }

}