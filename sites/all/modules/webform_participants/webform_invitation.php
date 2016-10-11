<?php
/*
version 1.0b-beta  20161030 -renamed to participants
1.0beta  20160930 : seems to work version
*/
/**
 * Implements hook_menu().
 */
function webform_participants_menu() {
  $items = array();
  $items['node/%webform_menu/webform/participants-settings'] = array(
    'title' => 'Participants Settings',
    'page callback' => 'webform_participants_settings_page',
    'page arguments' => array(1),
    'access callback' => 'node_access',
    'access arguments' => array('update', 1),
    'weight' => 11,
    'type' => MENU_LOCAL_TASK,
  );
    $items['node/%webform_menu/webform/participants-codes'] = array(
    'title' => 'Participants Codes',
    'page callback' => 'webform_participants_generate_page',
    'page arguments' => array(1),
    'access callback' => 'node_access',
    'access arguments' => array('update', 1),
    'weight' => 12,
    'type' => MENU_LOCAL_TASK,
  );
    $items['node/%webform_menu/webform/participants-reminder'] = array(
    'title' => 'Participants Reminder Mail',
    'page callback' => 'webform_participants_mail_reminder_page',
    'page arguments' => array(1),
    'access callback' => 'node_access',
    'access arguments' => array('update', 1),
    'weight' => 11,
    'type' => MENU_LOCAL_TASK,
  ); 
  $items['node/%webform_menu/webform/participants-download'] = array(
    'title' => 'Download Codes',
    'page callback' => 'webform_participants_download_file',
    'page arguments' => array(1),
    'access callback' => 'node_access',
    'access arguments' => array('update', 1),
  );
  return $items;
}

function webform_participants_settings_page($node) {
  $nid = $node->nid;
  $out = drupal_get_form('webform_participants_settings_form', $nid);
  return $out;
}

function webform_participants_settings_form($form, &$form_state, $nid) {
  $db_setting = db_select('webform_participants', 'i')
    ->fields('i')
    ->condition('nid', $nid, '=')
    ->execute()
    ->fetchAssoc();
  $form['nid'] = array(
    '#type' => 'hidden',
    '#value' => $nid,
  );
  $form['wi_enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable participants_module for this webform'),
    '#default_value' => $db_setting ? (int) $db_setting['participants'] : 0,
  );
  /*
  $form['mail_list'] = array( //160927 added by J
    '#type' => 'textarea',//160927 added by J
    '#title' => t('List of mails of receipiens for this webform'),
    '#default_value' => "",
  );
  */  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

function webform_participants_settings_form_submit($form, &$form_state) {
  global $base_url;
  $nid = $form_state['values']['nid'];
  $wi_enabled = $form_state['values']['wi_enabled'];
  if ($wi_enabled == 1) {
    drupal_set_message(t('Participants mode has been activated. You should now <a href="!base_url/node/!nid/participants/generate">create some Participants invitation codes</a>.', array('!base_url' => $base_url, '!nid' => $nid)));
    $node = node_load($nid);
    $field_present = false;
    foreach ($node->webform['components'] as $id => $com) {
      if ($com['form_key'] == 'webform_participants_code') {
        $field_present = true;
        $cid = $id;
        break;
      }
    }
    if ($field_present == false) {
      $cid = 1;
      if (is_array($node->webform['components']) && count($node->webform['components']) > 0) {
        $cid = max(array_keys($node->webform['components'])) + 1;
      }
      $code_box =  array(
        'nid' => $nid,
        'cid' => $cid,
        'pid' => 0,
        'form_key' => 'webform_participants_code',
        'name' => t('Participant Invite Code'),
        'type' => 'textfield',
        'value' => '[current-page:query:code]',
        'extra' => array(
          'description' => t('Enter your personal invitation code (only applies if the field is not populated yet).'),
          'title_display' => 'inline',
          'private' => 0,
          'disabled' => 0,
          'unique' => 1,
          'maxlength' => 64,
          'conditional_operator' => '=',
          'width' => '',
          'field_prefix' => '',
          'field_suffix' => '',
          'attributes' => array(),
          'conditional_component' => '',
          'conditional_values' => '',
        ),
        'required' => 1,
        'weight' => 0,
        'page_num' => 1,
      );
      $node->webform['components'][$cid] = $code_box;
      node_save($node);
    }
  }
  else {
    drupal_set_message(t('Participants Invitation mode has been disabled.'));
    $node = node_load($nid);
    foreach ($node->webform['components'] as $id => $com) {
      if ($com['form_key'] == 'webform_participants_code') {
        unset($node->webform['components'][$id]);
        node_save($node);
        break;
      }
    }
    $cid = 0;
  }
  db_merge('webform_participants')->key(array('nid' => $nid))
    ->fields(array(
      'participants' => $wi_enabled,
      'cid' => $cid,
      ))
    ->execute();
}

function webform_participants_codes_page($node) {
  global $base_url;
  $nid = $node->nid;
  $codes = db_select('webform_participants_codes', 'c')
    ->fields('c')
    ->condition('nid', $nid, '=')
    ->execute()->fetchAll();
  $out = "<h2>" . t('All Participant invitation codes for %node_title', array("%node_title" => $node->title)) . "</h2>";
  $out .= "<p><a href='" . $base_url . "/node/" . $nid . "/webform/participants-download'>" . t('Download Codes') . "</a></p>";
  if (count($codes) > 0) {
    $out .= "<table><tr><th>" . t('mail') . "</th><th>" . t('Code') . "</th><th>" . t('used?') . "</th><th>" . t('Submission ID') . "</th></tr>";//160927 added by J
  	foreach ($codes as $code) {
  	  $out .= '<tr><td>' . $code->mail . '</td><td>' . $code->code . '</td><td>' . ($code->used != NULL ? t('yes') : t('no')) . '</td><td>' . ($code->used != NULL ? l($code->sid,'node/' . $nid . '/submission/' . $code->sid) : '') . '</td></tr>';//160927 added by J
  	}
  	$out .= '</table>';
  }
  else {
    $out .= '<p><em>'.t('No codes present, yet. Click on "Generate" above to create codes.').'</em></p>';
  }
	return $out;
}

function webform_participants_generate_page($node) {
  $out = drupal_get_form('webform_participants_generate_form', $node);
  return $out;
}

function webform_participants_generate_form($form, &$form_state, $node) {
  $nid = $node->nid;
  $form['intro'] = array(
    '#markup' => '<h2>' . t('Generate new codes for %node_title', array("%node_title" => $node->title)) . '</h2><p>' . t('To generate codes please enter the required number of codes and hit the button.') . '</p>',
  );
  $form['nid'] = array(
    '#type' => 'hidden',
    '#value' => $nid,  
  );
  $form['number_of_tokens'] = array(
    '#type' => 'textfield',
    '#title' => t('# of codes to generate'),
    '#default_value' => array(
      25
    ),
    '#element_validate' => array('webform_participants_validate_numeric_count'),
    '#required' => true,
  );
  // added by J 160927++++++++++++++
  $form['participant_mails'] = array(
    '#type' => 'textarea',
    '#title' => t('paste email of the participants here (1 mail in each line)'),
    //'#default_value' => array(      25   ),
    //'#element_validate' => array('webform_participants_validate_numeric_count'),
    '#required' => false,
  );  
  // added by J 160927------------
  $form['options'] = array(
    '#type' => 'fieldset',
    '#title' => t('Options'),
    '#collapsible' => true,
    '#collapsed' =>false,
  );
  $form['options']['type_of_tokens'] = array(
    '#type' => 'radios',
    '#title' => t('type of tokens'),
    '#default_value' => 200,
    '#options' => array(200 => t('pasted_email_list'),1 => t('md5 hash (32 characters)'), 99 => t('custom')),
  );
  $form['options']['length_of_tokens'] = array(
    '#type' => 'textfield',
    '#title' => t('length of tokens (number of characters)'),
    '#default_value' => array(
      32
    ),
    '#element_validate' => array('webform_participants_validate_numeric_length'),
    '#states' => array('invisible' => array(':input[name="type_of_tokens"]' => array('value' => 1,'value' => 200))),
  );
  $form['options']['chars_of_tokens'] = array(
    '#type' => 'checkboxes',
    '#title' => t('characters to be used for tokens'),
    '#default_value' => array(
      1, 2, 3,
    ),
    '#options' => array(
      1 => t('lower case letters (a-z)'),
      2 => t('upper case letters (A-Z)'),
      3 => t('digits (0-9)'),
      4 => t('punctuation (.,:;-_!?)'),
      5 => t('special characters (#+*=$%&|)'),
    ),
    '#element_validate' => array('webform_participants_validate_option_count'),
    '#states' => array('invisible' => array(':input[name="type_of_tokens"]' => array('value' => 1,'value' => 200))),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Generate'),
  );

    
  $form['codes'] = array(
    '#markup' => '<div>' . webform_participants_codes_page($node) . '</div>',
  );
  return $form;
}


// ++++++++added 160928+++++mail_reminder++++++++++++++++++

function webform_participants_mail_reminder_page($node) {
  $out = drupal_get_form('webform_participants_mail_reminder_form', $node);
  return $out;
}

function webform_participants_mail_reminder_form($form, &$form_state, $node) {
  $nid = $node->nid;
  $form['intro'] = array(
    '#markup' => '<h2>' . t('Αποστολή email %node_title', array("%node_title" => $node->title)) . '</h2><p>' . t('To generate codes please enter the required number of codes and hit the button.') . '</p>',
  );
  $form['nid'] = array(
    '#type' => 'hidden',
    '#value' => $nid,  
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Αποστολή reminder mail '),
  );

  $form['codes'] = array(
    '#markup' => '<div>' . webform_participants_codes_page($node) . '</div>',
  );
  
  //	simple_mail_send("pliroforikos1@minedu.cu.cc", "pliroforikos@minedu.cu.cc","UEMA webform_participants_mail_reminder_form","ΣΩΜΑpliroforikos@c");  	
  
  return $form;
}


//---------added 160928--------mail_reminder----------------------------


//++++++++++++++added 160928++++++reminder mail+++


function webform_participants_mail_reminder_form_submit($form, &$form_state) {
//	simple_mail_send("pliroforikos1@minedu.cu.cc", "pliroforikos@minedu.cu.cc","UEMA webform_participants_mail_reminder_submit","ΣΩΜΑpliroforikos@c");  	
// /*
  global $base_url;

  //$nid = $node->nid;
  $nid = $form_state['values']['nid'];
  $node = node_load($nid);
  $codes = db_select('webform_participants_codes', 'c')
    ->fields('c')
    ->condition('nid', $nid, '=')
    ->execute()->fetchAll();


  	if (count($codes) > 0) {
		foreach ($codes as $code) {
//				if($code==null)continue;
/*
					$message = array(
				  'to' => '"'. addslashes(mime_header_encode('Test mail drupal 160929 ΔΟΚΙΜΗ')) .'" <'.$code->mail.'>',
				  'subject' => t('Ερωτηματολόγιο'),
				  'body' => 'Καλημέρα 001 '
				  .$code->fullname.' '
				  .$code->mail.' για να λάβετε μέρος στο survey θέλετε τον κωδικό ' 
				  .$code->code. '  link : ' 
				  . $base_url 
				  . "/node/" . $nid ,
				  'headers' => array(
						  'From' => 'plirodorikos0+drupal@gmail.com',
						  'MIME-Version' => '1.0',
						  'Content-Type' => 'text/html;charset=utf-8',),
				);
				*/
				$body= 'Καλημέρα. '
				  .$code->fullname.' '
				  .$code->mail.' για να λάβετε μέρος στο survey θέλετε τον κωδικό <b> ' 
				  .$code->code. '  link : ' 
				  . $base_url 
				  . "/node/" . $nid."?code=".$code->code;
				  //To DO Πάρε το όνομα του ερωτηματολογίου και βάλτο στο Θέμα του μαιλ
				$subject=   t('Ερωτηματολόγιο :'.$node->title);
				//$to='"'. addslashes(mime_header_encode('Test mail drupal 160930c ΔΟΚΙΜΗ')) .'" <'.$code->mail.'>';
				$to=$code->mail;
				//$to="pliroforikos@minedu.cu.cc";
				$from= 'plirodorikos0+drupal@gmail.com';
				//drupal_mail_send($message);
				// simple_mail_send($from, $to, $subject, $body);
				if(strlen($code->used)==0|| is_null($code->used))	{
					//To DO : change to simple_mail_queue()
					simple_mail_send($from, $to, $subject, $body); //must add if used is not null
					    drupal_set_message(t("simple_sendMAIL:$to SUBJECT:$subject.")); //DEBUG
				}
				
	}//end of foreach


//	simple_mail_send("pliroforikos1@minedu.cu.cc", "pliroforikos@minedu.cu.cc","UEMA pliroforikos@minedu.cu.cc","ΣΩΜΑpliroforikos@c");  	

  }
  else {
    //$out .= '<p><em>'.t('No codes present, yet. Click on "Generate" above to create codes.').'</em></p>';
  }
// */

  drupal_goto('node/' . $nid . '/webform/participants-reminder');
}



//----------added 160928-------reminder mail submit


function webform_participants_validate_numeric_count($element, &$form_state) {
  if (!preg_match('/^\d+$/', $element['#value'])) {
   form_error($element, t('Enter an integer only.'));
  }
  elseif ($element['#value'] < 0) {
    form_error($element, t('Enter a number greater than zero.'));
  }
}

function webform_participants_validate_numeric_length($element, &$form_state) {
  if (!preg_match('/^\d+$/', $element['#value'])) {
   form_error($element, t('Enter an integer only.'));
  }
  elseif ($element['#value'] < 5 || $element['#value'] > 64) {
    form_error($element, t('The length must be between 5 and 64.'));
  }
}

function webform_participants_validate_option_count($element, &$form_state) {
  if (count($element['#value']) == 0) {
   form_error($element, t('Choose at least one character subset.'));
  }
}

function webform_participants_generate_form_submit($form, &$form_state) {
  $number = $form_state['values']['number_of_tokens']; 
  // $number = $count_lines_of_copied_text_with_mails;  //160927 J Implement this
  $nid = $form_state['values']['nid'];
  if ($form_state['values']['type_of_tokens'] == 1) { // MD5 option
    $i = $l = 1;
    while ($i <= $number && $l < $number * 10) {
      // Code generation
      $code = md5(microtime(1) * rand());
      try {
        // Insert code to DB
        $tmpres = db_insert('webform_participants_codes')->fields(array(
          'nid' => $nid,
          'code' => $code,
          'time_generated' => REQUEST_TIME,
          'used' => NULL,
          'sid' => 0,
        ))->execute();
        $i++;
      }
      catch (PDOException $e) {
        // The generated code is already in DB; make another one.
      }
      $l++;
    }
  }
  
  elseif ($form_state['values']['type_of_tokens'] == 99) {
    $length = $form_state['values']['length_of_tokens'];
    $char_sets = $form_state['values']['chars_of_tokens'];
    $chars = '';
    if (in_array(1, $char_sets)) {
      $chars .= 'abcdefghijklmnopqrstuvwxyz';
    }
    if (in_array(2, $char_sets)) {
      $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if (in_array(3, $char_sets)) {
      $chars .= '0123456789';
    }
    if (in_array(4, $char_sets)) {
      $chars .= '.,:;-_!?';
    }
    if (in_array(5, $char_sets)) {
      $chars .= '#+*=$%&|';
    }
    $i = $l = 1;
    while ($i <= $number && $l < $number * 10) {
      // Code generation
      $code = '';
      for ($j = 1; $j <= $length; $j++) {
        $code .= $chars[rand(0, strlen($chars)-1)];
      }
      try {
        $tmpres = db_insert('webform_participants_codes')->fields(array(
          'nid' => $nid,
          'code' => $code,
          'time_generated' => REQUEST_TIME,
          'used' => NULL,
          'sid' => 0
        ))->execute();
        $i++;
      }
      catch (PDOException $e) {
        // The generated code is already in DB; make another one.
      }
      $l++;
    }
  }
  //added by J 160927++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
  elseif ($form_state['values']['type_of_tokens'] == 200) {
	
	  $string=$form_state['values']['participant_mails'];
	  // this regex handles more email address formats like a+b@google.com.sg, and the i makes it case insensitive
	//$pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i'; //orig
$pattern="//";

	// preg_match_all returns an associative array
	preg_match_all($pattern, $string, $matches);
	$matches[0] = explode(PHP_EOL, $string);

	// the data you want is in $matches[0], dump it with var_export() to see it
	//var_export($matches[0]);
	  
	  $number = count($matches[0]);  //160927 J Implement this
    $i = $l = 1;
    while ($i <= $number && $l < $number * 10) {
      // Code generation
      $code = md5(microtime(1) * rand());
      //preg_match_all($pattern, $string, $matches[0][$i-1]);
      //$matches[0][$i]=$string; // HARD CODED added by J DEBUG
      !!filter_var($matches[0][$i-1], FILTER_VALIDATE_EMAIL);
      try {
        // Insert code to DB
        $tmpres = db_insert('webform_participants_codes')->fields(array(
          'nid' => $nid,
          'code' => $code,
       //   'mail' => $matches[0][$i], //added by J 160927
//          'mail' => $matches[0][$i-1], //added by J 160927 ok working with explode
           'mail' => $matches[0][$i-1], //added by J 160927
          'time_generated' => REQUEST_TIME,
          'used' => NULL,
          'sid' => 0,
        ))->execute();
        $i++;
      }
      catch (PDOException $e) {
        // The generated code is already in DB; make another one.
        
      }
      $l++;
    }	  
  }
  //added by J 160927------------------------------------------------------------
  $codes_count = $i - 1;
  if ($l >= $number * 10) {
    drupal_set_message(t('Due to unique constraint, only @ccount codes have been generated.', array('@ccount' => $codes_count)),'error');
  }
  elseif ($codes_count == 1) {
    drupal_set_message(t('A single code has been generated.'),'status');
  }
  elseif ($codes_count >= 2) {
    drupal_set_message(t('A total of @ccount codes has been generated.', array('@ccount' => $codes_count)),'status');
  }
  drupal_goto('node/' . $nid . '/webform/participants-codes');
}

function webform_participants_form_alter(&$form, &$form_state, $form_id) {
  if (substr($form_id,0,20) == 'webform_client_form_') {
    $nid = $form['#node']->nid;
    if ($nid > 0) {
      $db_setting = db_select('webform_participants', 'i')
      ->fields('i')
      ->condition('nid', $nid, '=')
      ->execute()
      ->fetchAssoc();
      if ($db_setting['participants'] == "1") {
        $form['#validate'][] = 'webform_participants_code_validate';
      }
    }
  }
  return $form;
}

function webform_participants_code_validate($form, &$form_state) {
  if (isset($form_state['values']['submitted']['webform_participants_code'])) {
    $code = $form_state['values']['submitted']['webform_participants_code'];
    $result = db_select('webform_participants_codes', 'c')
      ->fields('c')
      ->condition('code', $code, '=')
      ->execute()
      ->fetchAssoc();
    if (!isset($result) || $result == NULL) {
      form_set_error('webform_participants_code', t('This code is not valid.'));
    }//(  //comment by J
    elseif ($result['used'] > 0) {
      // Not required, handled by webform => UNIQUE option.
      #form_set_error('participants_code', 'This code has already been used.');
    }
    else {
      // valid code, update db
      $num = db_update('webform_participants_codes')
        ->fields(array(
          'used' => REQUEST_TIME,
        ))
        ->condition('code', $code, '=')
        ->execute();
    }
  }
}

function webform_participants_webform_submission_insert($node, $submission) {
  $db_setting = db_select('webform_participants', 'i')
    ->fields('i')
    ->condition('nid', $node->nid, '=')
    ->execute()
    ->fetchAssoc();
  if ($db_setting && (int) $db_setting['participants'] == 1) {
    if ($db_setting['cid'] > 0) {
      $cid = $db_setting['cid'];
    }
    else {
      $node = node_load($result['nid']);
      foreach ($node->webform['components'] as $id => $com) {
        if ($com['form_key'] == 'webform_participants_code') {
          $cid = $id;
          break;
        }
      }
    }
    db_update('webform_participants_codes')
      ->fields(array(
        'sid' => $submission->sid,
      ))
      ->condition('code',$submission->data[$cid][0])
      ->execute();
  }
}

function webform_participants_download_file($node) {
  global $base_url;
  $nid = $node->nid;
  //this is the XLS header:
  $xlshead = pack("s*", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
  //this is the XLS footer:
  $xlsfoot = pack("s*", 0x0A, 0x00);
  
  $codes = db_select('webform_participants_codes', 'c')
  ->fields('c')
  ->condition('nid', $nid, '=')
  ->execute();
  $data = "";
  $row = 0;
  while ($code = $codes->fetchAssoc()) {
    $data .= webform_participants_xlsCell($row, 0, $base_url . "/" . drupal_get_path_alias("node/" . $nid) . '?code=' . $code['code']);
    $row++;
  }
  $filename="codes-".$nid.".xls";
  header("Content-Type: application/force-download");
  header("Content-Type: application/octet-stream");
  header("Content-Type: application/download");;
  header("Content-Disposition: attachment;filename=$filename");
  header("Content-Transfer-Encoding: binary ");
  echo $xlshead . $data . $xlsfoot;
  exit; //this is important!
}

function webform_participants_xlsCell($row,$col,$val) {
  $len = strlen($val);
  return pack("s*", 0x204, 8+$len, $row, $col, 0x0, $len) . $val;
}
