<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID;

    $page = tep_output_string($page);

    if (!tep_not_null($page)) {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
    }

    if (tep_not_null($parameters)) {
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (tep_not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = tep_session_name() . '=' . tep_session_id();
        }
      }
    }

    if (isset($_sid)) {
      $link .= $separator . tep_output_string($_sid);
    }

    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);
    } else {
      $link = str_replace('&', '&amp;', $link);
    }

    return $link;
  }
  
	function tep_link_object($link, $object, $parameters = null) {
		return '<a href="' . $link . '"' . (!empty($parameters) ? ' ' . $parameters : '') . '>' . $object . '</a>';
	}	
////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '', $responsive = true, $bootstrap_css = '') {
    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . tep_output_string($src) . '" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title="' . tep_output_string($alt) . '"';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = intval($image_size[0] * $ratio);
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = intval($image_size[1] * $ratio);
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    $image .= ' class="';

    if (tep_not_null($responsive) && ($responsive === true)) {
      $image .= 'img-responsive';
    }

    if (tep_not_null($bootstrap_css)) $image .= ' ' . $bootstrap_css;

    $image .= '"';

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '') {
    global $language;

    $image_submit = '<input type="image" src="' . tep_output_string(DIR_WS_LANGUAGES . $language . '/images/buttons/' . $image) . '" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_output_string($alt) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= ' />';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {
    global $language;

    return tep_image(DIR_WS_LANGUAGES . $language . '/images/buttons/' . $image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '', $tokenize = false) {
    global $sessiontoken;

    $form = '<form name="' . tep_output_string($name) . '" action="' . tep_output_string($action) . '" method="' . tep_output_string($method) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    if ( ($tokenize == true) && isset($sessiontoken) ) {
      $form .= '<input type="hidden" name="formid" value="' . tep_output_string($sessiontoken) . '" />';
    }

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true, $class = 'class="form-control"') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if ( ($reinsert_value == true) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $value = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $value = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    if (tep_not_null($class)) $field .= ' ' . $class;

    $field .= ' />';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = '') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name]) && (($HTTP_GET_VARS[$name] == 'on') || (stripslashes($HTTP_GET_VARS[$name]) == $value))) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name]) && (($HTTP_POST_VARS[$name] == 'on') || (stripslashes($HTTP_POST_VARS[$name]) == $value))) ) {
      $selection .= ' checked="checked"';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
// The $wrap parameter is no longer used in the core xhtml template
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<textarea class="form-control" name="' . tep_output_string($name) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( ($reinsert_value == true) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $field .= tep_output_string_protected(stripslashes($HTTP_GET_VARS[$name]));
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $field .= tep_output_string_protected(stripslashes($HTTP_POST_VARS[$name]));
      }
    } elseif (tep_not_null($text)) {
      $field .= tep_output_string_protected($text);
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) {
      if ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) ) {
        $field .= ' value="' . tep_output_string(stripslashes($HTTP_GET_VARS[$name])) . '"';
      } elseif ( (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) {
        $field .= ' value="' . tep_output_string(stripslashes($HTTP_POST_VARS[$name])) . '"';
      }
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

////
// Hide form elements
  function tep_hide_session_id() {
    global $session_started, $SID;

    if (($session_started == true) && tep_not_null($SID)) {
      return tep_draw_hidden_field(tep_session_name(), tep_session_id());
    }
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<select name="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' class="form-control">';

    if (empty($default) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $default = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $default = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

////
// Creates a pull-down list of countries
  function tep_get_country_list($name, $selected = '', $parameters = '') {
    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
    $countries = tep_get_countries();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }

    return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }

////
// Output a jQuery UI Button
  function tep_draw_button($title = null, $icon = null, $link = null, $priority = null, $params = null, $style = null) {
    static $button_counter = 1;

    $types = array('submit', 'button', 'reset');

    if ( !isset($params['type']) ) {
      $params['type'] = 'submit';
    }

    if ( !in_array($params['type'], $types) ) {
      $params['type'] = 'submit';
    }

    if ( ($params['type'] == 'submit') && isset($link) ) {
      $params['type'] = 'button';
    }

    if (!isset($priority)) {
      $priority = 'secondary';
    }

    $button = NULL;

    if ( ($params['type'] == 'button') && isset($link) ) {
      $button .= '<a id="btn' . $button_counter . '" href="' . $link . '"';

      if ( isset($params['newwindow']) ) {
        $button .= ' target="_blank"';
      }
    } else {
      $button .= '<button ';
      $button .= ' type="' . tep_output_string($params['type']) . '"';
    }

    if ( isset($params['params']) ) {
      $button .= ' ' . $params['params'];
    }

    $button .= ' class="btn ';

    $button .= (isset($style)) ? $style : 'btn-default';

    $button .= '">';

    if (isset($icon) && tep_not_null($icon)) {
      $button .= ' <span class="' . $icon . '"></span> ';
    }

    $button .= $title;

    if ( ($params['type'] == 'button') && isset($link) ) {
      $button .= '</a>';
    } else {
      $button .= '</button>';
    }

    $button_counter++;

    return $button;
  }

  // review stars
  function tep_draw_stars($rating = 0, $meta = false) {
    $stars = str_repeat('<span class="glyphicon glyphicon-star"></span>', (int)$rating);
    $stars .= str_repeat('<span class="glyphicon glyphicon-star-empty"></span>', 5-(int)$rating);
    if ($meta !== false) $stars .= '<meta itemprop="rating" content="' . (int)$rating . '" />';

    return $stars;
  }

  function tep_navbar_search($btnclass ='btn-default', $description = true) {
    global $request_type;

    $search_link = '<div class="searchbox-margin">';
    $search_link .= tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', $request_type, false), 'get', 'class="form-horizontal"');
    $search_link .= '    <div class="input-group">' .
                            tep_draw_input_field('keywords', '', 'required placeholder="' . TEXT_SEARCH_PLACEHOLDER . '"', 'search') .
                     '        <span class="input-group-btn"><button type="submit" class="btn ' . $btnclass .'"><i class="glyphicon glyphicon-search"></i></button></span>' .
                     '    </div>';
    $search_link .= '</div>';
    if (tep_not_null($description) && ($description === true)) {
      $search_link .= tep_draw_hidden_field('search_in_description', '1');
    }
    $search_link .=  tep_hide_session_id() . '</form>';

	  return $search_link;
  }
  
/**
 * Outputs a form selection field (checkbox/radio)
 *
 * @param string $name The name and indexed ID of the selection field
 * @param string $type The type of the selection field (checkbox/radio)
 * @param mixed $values The value of, or an array of values for, the selection field
 * @param string $default The default value for the selection field
 * @param string $parameters Additional parameters for the selection field
 * @param string $separator The separator to use between multiple options for the selection field
 * @access public
 */

  function osc_draw_selection_field($name, $type, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    if (!is_array($values)) {
      $values = array($values);
    }

    if ( strpos($name, '[') !== false ) {
      $name_string = substr($name, 0, strpos($name, '['));

      if ( isset($_GET[$name_string]) ) {
        $default = $_GET[$name_string];
      } elseif ( isset($_POST[$name_string]) ) {
        $default = $_POST[$name_string];
      }
    } else {
      if ( isset($_GET[$name]) ) {
        $default = $_GET[$name];
      } elseif ( isset($_POST[$name]) ) {
        $default = $_POST[$name];
      }
    }

    $field = '';

    $counter = 0;

    foreach ($values as $key => $value) {
      $counter++;

      if (is_array($value)) {
        $selection_value = $value['id'];
        $selection_text = '&nbsp;' . $value['text'];
      } else {
        $selection_value = $value;
        $selection_text = '';
      }

      $field .= '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

      if (strpos($parameters, 'id=') === false) {
        $field .= ' id="' . tep_output_string($name) . (sizeof($values) > 1 ? '_' . $counter : '') . '"';
      } elseif (sizeof($values) > 1) {
        $offset = strpos($parameters, 'id="');
        $field .= ' id="' . tep_output_string(substr($parameters, $offset+4, strpos($parameters, '"', $offset+4)-($offset+4))) . '_' . $counter . '"';
      }

      if (trim($selection_value) != '') {
        $field .= ' value="' . tep_output_string($selection_value) . '"';
      }

      if ((is_bool($default) && $default === true) || ((is_string($default) && (trim($default) == trim($selection_value))) || (is_array($default) && in_array(trim($selection_value), $default)))) {
        $field .= ' checked="checked"';
      }

      if (!empty($parameters)) {
        $field .= ' ' . $parameters;
      }

      $field .= ' />';

      if (!empty($selection_text)) {
        $field .= '<label for="' . tep_output_string($name) . (sizeof($values) > 1 ? $counter : '') . '" class="fieldLabel">' . $selection_text . '</label>';
      }

      $field .= $separator;
    }

    if (!empty($field)) {
      $field = substr($field, 0, strlen($field)-strlen($separator));
    }

    return $field;
  }
/**
 * Outputs a form pull down menu field
 *
 * @param string $name The name of the pull down menu field
 * @param array $values Defined values for the pull down menu field
 * @param string $default The default value for the pull down menu field
 * @param string $parameters Additional parameters for the pull down menu field
 * @access public
 */

  function osc_draw_pull_down_menu($name, $values, $default = null, $parameters = null) {
    $group = false;

    if (isset($_GET[$name])) {
      $default = $_GET[$name];
    } elseif (isset($_POST[$name])) {
      $default = $_POST[$name];
    }

    $field = '<select class="form-control" name="' . tep_output_string($name) . '"';

    if (strpos($parameters, 'id=') === false) {
      $field .= ' id="' . tep_output_string($name) . '"';
    }

    if (!empty($parameters)) {
      $field .= ' ' . $parameters;
    }

    $field .= '>';

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      if (isset($values[$i]['group'])) {
        if ($group != $values[$i]['group']) {
          $group = $values[$i]['group'];

          $field .= '<optgroup label="' . tep_output_string($values[$i]['group']) . '">';
        }
      }

      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';

      if ( (!is_null($default) && !is_array($default) && ((string)$default == (string)$values[$i]['id'])) || (is_array($default) && in_array($values[$i]['id'], $default)) ) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';

      if ( ($group !== false) && (($group != $values[$i]['group']) || !isset($values[$i+1])) ) {
        $group = false;

        $field .= '</optgroup>';
      }
    }

    $field .= '</select>';

    return $field;
  }
/**
 * Outputs a form checkbox field
 *
 * @param string $name The name and indexed ID of the checkbox field
 * @param mixed $values The value of, or an array of values for, the checkbox field
 * @param string $default The default value for the checkbox field
 * @param string $parameters Additional parameters for the checkbox field
 * @param string $separator The separator to use between multiple options for the checkbox field
 * @access public
 */

  function osc_draw_checkbox_field($name, $values = null, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    return osc_draw_selection_field($name, 'checkbox', $values, $default, $parameters, $separator);
  }

/**
 * Outputs a form radio field
 *
 * @param string $name The name and indexed ID of the radio field
 * @param mixed $values The value of, or an array of values for, the radio field
 * @param string $default The default value for the radio field
 * @param string $parameters Additional parameters for the radio field
 * @param string $separator The separator to use between multiple options for the radio field
 * @access public
 */

  function osc_draw_radio_field($name, $values, $default = null, $parameters = null, $separator = '&nbsp;&nbsp;') {
    return osc_draw_selection_field($name, 'radio', $values, $default, $parameters, $separator);
  }  
