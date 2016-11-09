<?php

/**
 * @file
 * Token module integration.
 */

/**
 * Implements hook_token_info_alter().  //jon: help text for CAS attributes
 */
function cas_attributes_token_info_alter(&$data) {
  if (function_exists('cas_phpcas_attributes')) {
    $data['tokens']['cas']['attribute'] = array(
      'name' => t('Attribute'),
      'description' => t('A CAS attribute of the user. (Only available if user is logged into CAS). Always stored as an array token (thus supporting multivalue attributes); therefore be sure to remember to specify which array member you want (for instance, to get the first value: [cas:attribute:?:first]).  <a href="@url">Available tokens</a>.', array('@url' => url('admin/config/people/cas/attributes/cas'))),
      'dynamic' => TRUE,
    );
  }
}

/**
 * Implements hook_tokens().
 */
function cas_attributes_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $sanitize = !empty($options['sanitize']);
  $replacements = array();
  $debug_me=false;
  if ($type == 'cas' && !empty($data['cas'])) {
    $cas = $data['cas'];

    // Provide [cas:attribute:?] dynamic tokens.
    if ($attribute_tokens = token_find_with_prefix($tokens, 'attribute')) {
      $attribute = array_change_key_case(cas_phpcas_attributes($cas));

      //parse_lower_array($attribute);//debug JON 161104
        //  if($debug_me)  drupal_set_message("attribute_tokens : ".'<pre>'. print_r($attribute_tokens, true) .'</pre>');   //debug JON 161104
       //   if($debug_me)  drupal_set_message("attribute : ".'<pre>'. print_r($attribute, true) .'</pre>');   //debug JON 161104

// if($debug_me)  drupal_set_message("attribute_tokens HARDCODED :  "."<pre> $name = $attribute[$name] </pre>");   //debug JON 161104
 if($debug_me)  drupal_set_message("HARDCODED  :  <pre> edupersonorgunitdn:gsnunitcode:extended = ".print_r($attribute['edupersonorgunitdn:gsnunitcode:extended'], true)." </pre>");   //debug JON 161104
 if($debug_me)  drupal_set_message("HARDCODED  :  <pre> edupersonorgunitdn:gsnunitcode = ".print_r($attribute['edupersonorgunitdn:gsnunitcode'], true)." </pre>");   //debug JON 161104




      foreach ($attribute_tokens as $name => $original) {
       if($debug_me)  drupal_set_message("QQQQQQQnames  : ".'<pre>'. print_r($name, true) .'</pre>');   //debug JON 161104
       if($debug_me)  drupal_set_message("QQQQQQQoriginal  : ".'<pre>'. print_r($original, true) .'</pre>');   //debug JON 161104
        // If there are no options specified have it return all values.
        if (strpos($name, ':') === FALSE) {
        // if($debug_me)  drupal_set_message("level 0.0 : ".$name);   //debug JON 161104
          $name .= ':join';
        }


        // Break out the token into attributes and the options for them.
        //list($name, $token) = explode(':', $name, 2); //ORIG LINE
        list($name, $token) = explode(':', $name, 2);

          if($debug_me)  drupal_set_message("names got from list : ".'<pre>'. print_r($name, true) .'</pre>');   //debug JON 161104

        $name = drupal_strtolower($name);

        if (isset($attribute[$name])) {
          $value = $attribute[$name];
          //if it is plain value make it an array
          if (!is_array($value)) {
             if($debug_me)  drupal_set_message("pass A (not array) : ".$name." = ".$value);   //debug JON 161104
            $value = array($value);
          } else { // IS AN ARRAY SO WE PARSE IT

            //+++++++++++++++++++++++++++++++++++++++

             if($debug_me)  drupal_set_message("pass A ( IS array) : ".$name." = ".'<pre>'. print_r($attribute[$name], true) .'</pre>');   //debug JON 161104
            //parse_lower_array($attribute);//debug JON 161104
            //parse_lower_array($attribute[$name]);//debug JON 161104

            //----------------------------------------
          }
          //jon note !!! $original is [current-user:cas:attribute:edupersonorgunitdn:cn:extended]  , $name=edupersonorgunitdn:cn:extended
          //jon note : should change $original to eg [current-user:cas:attribute:edupersonorgunitdn-cn-extended]
          $replacements += token_generate('array', array($token => $original), array('array' => $value), $options);

        } // end of if (isset($attribute[$name])) {
        elseif ($name == '?') {
          $keys = array_keys($attribute);
          if ($sanitize) {
            $keys = array_map('check_plain', $keys);
          }
          $replacements[$original] = t('Available attributes: %keys', array('%keys' => implode(', ', $keys)));
        }  // end of elseif ($name == '?') {
      } // end of  foreach ($attribute_tokens as $name => $original) {

    }
    $replacements = add_fixed_tokens($attribute,$replacements,$options,$token);
  }

   if($debug_me)  drupal_set_message("replacements : ".'<pre>'. print_r($replacements, true) .'</pre>');   //debug JON 161104
  return $replacements;
}
//++++++++++++++++++++++++++++++++++++++++++++++++
function add_fixed_tokens($attribute,$replacements,$options,$token){

$debug_me=false;
 if($debug_me)  drupal_set_message("HARDCODED  :  <pre> edupersonorgunitdn:gsnunitcode:extended = ".print_r($attribute['edupersonorgunitdn:gsnunitcode:extended'], true)." </pre>");   //debug JON 161104
 if($debug_me)  drupal_set_message("HARDCODED  :  <pre> edupersonorgunitdn:gsnunitcode = ".print_r($attribute['edupersonorgunitdn:gsnunitcode'], true)." </pre>");   //debug JON 161104

          //jon note !!! $original is [current-user:cas:attribute:edupersonorgunitdn:cn:extended]  , $name=edupersonorgunitdn:cn:extended
          //jon note : should change $original to eg [current-user:cas:attribute:edupersonorgunitdn-cn-extended]

$original= '[current-user:cas:attribute:edupersonorgunitdn-gsnunitcode-extended]';
$value=$attribute['edupersonorgunitdn:gsnunitcode:extended'];
if (!is_array($value)) {            $value = array($value);          }
$replacements += token_generate('array', array($token => $original), array('array' => $value), $options);

$original= '[current-user:cas:attribute:edupersonorgunitdn-gsnunitcode]';
$value=$attribute['edupersonorgunitdn:gsnunitcode'];
if (!is_array($value)) {            $value = array($value);          }
$replacements += token_generate('array', array($token => $original), array('array' => $value), $options);


$original= '[current-user:cas:attribute:edupersonorgunitdn-cn-extended]';
$value=$attribute['edupersonorgunitdn:cn:extended'];
if (!is_array($value)) {            $value = array($value);          }
$replacements += token_generate('array', array($token => $original), array('array' => $value), $options);



if($debug_me)  drupal_set_message("add_fixed_tokens replacements : ".'<pre>'. print_r($replacements, true) .'</pre>');   //debug JON 161104
 return $replacements;

}


function parse_lower_array($attributes) {
 $debug_me=true;
//   if($debug_me)  drupal_set_message("function parse_lower_array attribute : ".'<pre>'. print_r($attributes['edupersonorgunitdn:gsnunitcode'], true) .'</pre>');   //debug JON 161104




//   if($debug_me)  drupal_set_message("function parse_lower_array attribute 0 : ".'<pre>'. print_r($attributes[0], true) .'</pre>');   //debug JON 161104

/*
  foreach ($attributes as $attribute => $value) {

    if (is_array($value)) {
      $html = "";
      foreach ($value as $k => $v) {
         if($debug_me)  drupal_set_message("SSSSScas_attrib_admin k=$k ,  v=".$v );   //debug JON 161104  //all results are here

        $html .= check_plain($v) . '<br />';
      }
      // if($debug_me)  drupal_set_message("cas_attrib_admin html : ".$html);   //debug JON 161104  //all results are here

      $rows[] = array(
        t('[cas:attribute:@attribute]',
          array('@attribute' => drupal_strtolower($attribute))),

        $html
      );
      if (substr_count( $attribute, ':')>0)  {
        if($debug_me)  drupal_set_message("substr_count : ".$attribute);   //debug JON 161104

       }
    } else {
      $rows[] = array(
        t('[cas:attribute:@attribute]',
          array('@attribute' => drupal_strtolower($attribute))),
        check_plain($value),
      );
      if (substr_count( $attribute, ':')>0)  {


        if($debug_me)  drupal_set_message("B ELSE substr_count : ".$attribute);   //debug JON 161104

       }
    }
  }
 if($debug_me)  drupal_set_message("END OF parse_lower_array_JON cas_attrib_admin print_r rows1 : ".'<pre>'. print_r($rows, true) .'</pre>');   //debug JON 161104  //all results are here
*/
}
//------------------------------------------------