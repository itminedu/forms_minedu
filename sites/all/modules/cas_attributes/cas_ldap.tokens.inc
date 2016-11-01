<?php

/**
 * @file
 * Token module integration.
 */

/**
 * Implements hook_token_info_alter().
 */
function cas_ldap_token_info_alter(&$data) {
  $data['tokens']['cas']['ldap'] = array(
    'name' => t('LDAP'),
    'description' => t('An LDAP attribute of the CAS user. <a href="@url">Available tokens</a>.', array('@url' => url('admin/config/people/cas/attributes/ldap'))),
    'dynamic' => TRUE,
  );
}

/**
 * Implements hook_tokens().
 */
function cas_ldap_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $sanitize = !empty($options['sanitize']);
  $replacements = array();

  if ($type == 'cas' && !empty($data['cas'])) {
    $cas = $data['cas'];

    // Provide [cas:attribute:?] dynamic tokens.
    if ($attribute_tokens = token_find_with_prefix($tokens, 'ldap')) {
      $attribute = cas_ldap_attributes($cas);
      foreach ($attribute_tokens as $name => $original) {
        $name = drupal_strtolower($name);
        if (isset($attribute[$name])) {
          $value = $attribute[$name];
          if (is_array($value)) {
            $value = $value[0];
          }
          // Values that are not valid UTF-8 cannot be run through
          // check_plain(). Convert these to a string. Normally, this would be a
          // GUID, so format as such.
          if ($sanitize && !drupal_validate_utf8($value)) {
            $value = cas_ldap_guid_bin2hex($value);
          }
          $replacements[$original] = $sanitize ? check_plain($value) : $value;
        }
        elseif ($name == '?') {
          $keys = array_keys($attribute);
          if ($sanitize) {
            $keys = array_map('check_plain', $keys);
          }
          $replacements[$original] = t('Available attributes: %keys', array('%keys' => implode(', ', $keys)));
        }
      }
    }
  }

  return $replacements;
}

/**
 * Converts a binary GUID into a string representation.
 *
 * @param $guid binary
 *   The GUID.
 *
 * @return string
 *   The safe string representation of the GUID.
 */
function cas_ldap_guid_bin2hex($guid) {
  $guid = bin2hex($guid);

  // Format $guid so it matches what other LDAP tool displays for AD GUIDs.
  // If $guid is not the normal length for a GUID, do not format.
  if (strlen($guid) === 32) {
    $guid = strtoupper(substr($guid, 6, 2) . substr($guid, 4, 2) . substr($guid, 2, 2) . substr($guid, 0, 2) . '-' . substr($guid, 10, 2) . substr($guid, 8, 2) . '-' . substr($guid, 14, 2) . substr($guid, 12, 2) . '-' . substr($guid, 16, 4) . '-' . substr($guid, 20));
  }

  return $guid;
}
