<?php

include 'AmoEnums.php';
include 'AmoAPI.php';
include 'conf.php';

if (!isset($argv[1])){
	die("need method!\n");
}

$method = $argv[1];

switch ($method){

case "create":

	if (!isset($argv[2])){
		die("Pass # of entities to create!!\n");
	}
	if ($argv[2] <= 0 || $argv[2] > AMO_CREATE_LIMIT){
		die("Pass valid # of entities to create!!\n");
	}

	$amount = $argv[2];

	$amo = new AmoAPI(AMO_AUTH_LOGIN, AMO_AUTH_HASH, AMO_SUBDOMAIN, AMO_COOKIE_FILE, AMO_DEBUG);
	if (!$amo->auth()){
		die("Can't auth!!\n");
	}
	$field = $amo->check_field_exists(AMO_MULTI_FIELD_NAME);
	if (!$field){
		$field_values = array();
		for ($i = 0; $i < AMO_FIELD_ENUM_Q; $i++){
			$field_values[] = rand(0, 100000);
		}
		$field = $amo->create_custom_field(AMO_MULTI_FIELD_NAME, AMO_FIELD_TYPE, 
			AMO_FIELD_ENTITY_TYPE, AMO_FIELD_ORIGIN, $field_values);
	}
	echo "company  lead    contact\n";
	for ($i = 0; $i < $amount; $i++){
		$rand = rand(0, 1000);
		$company = $amo->create_company("company {$rand}");

		$custom_fields = array(
			array(
				'id' => $field['id'],
				'values' => array(
					'value' => array_rand($field['enums'])
				)
			)
		);
		$lead = $amo->create_lead("lead {$rand}");
		$contact = $amo->create_contact("contact {$rand}", array($lead[0]['id']), array($company[0]['id']), $custom_fields);
		echo "{$company[0]['id']} {$lead[0]['id']} {$contact[0]['id']}\n";
	}
	break;

case 'set_text_field':
	if (!isset($argv[2])){
		die("Pass ID of entity!!\n");
	}
	if (!isset($argv[3])){
		die("Pass value of field!!\n");
	}

	$entity_id = $argv[2];
	$value = $argv[3];

	$amo = new AmoAPI(AMO_AUTH_LOGIN, AMO_AUTH_HASH, AMO_SUBDOMAIN, AMO_COOKIE_FILE, AMO_DEBUG);
	if (!$amo->auth()){
		die("Can't auth!!\n");
	}

	$entity = $amo->get_contact_by_id($entity_id);
	$type = AmoEnums::entity_field_types['contact'];
	if (!$entity){
		$entity = $amo->get_lead_by_id($entity_id);
		$type = AmoEnums::entity_field_types['lead'];
		if (!$entity){
			$entity = $amo->get_company_by_id($entity_id);
			$type = AmoEnums::entity_field_types['company'];
			if (!$entity){
				die("Can't find an entity!!\n");
			}
		}
	}
	$field = $amo->check_field_exists(AMO_TEXT_FIELD_NAME, 0, array_search($type, AmoEnums::entity_field_types));
	if (!$field){
		$field = $amo->create_custom_field(AMO_TEXT_FIELD_NAME, AmoEnums::custom_field_types['TEXT'], 
			$type, AMO_FIELD_ORIGIN);
	}
	if (isset($field[0])){
		$field = $field[0];
	}
	$custom_fields = array(
		array( 'id' => $field['id'],
		'values' => array(
			array(
				'value' => $value
			))
		)
	);

	switch ($type){
		case AmoEnums::entity_field_types['contact']:
			die(print_r($amo->update_contact($entity[0]['id'], $custom_fields), TRUE));
			break;
		case AmoEnums::entity_field_types['company']:
			die(print_r($amo->update_company($entity[0]['id'], $custom_fields), TRUE));
			break;
		case AmoEnums::entity_field_types['lead']:
			die(print_r($amo->update_lead($entity[0]['id'], $custom_fields), TRUE));
			break;
	}
	break;
case 'add_note':
	if (!isset($argv[2])){
		die("Pass ID of entity!!\n");
	}
	if (!isset($argv[3])){
		die("Pass value of field!!\n");
	}
	if (!in_array($argv[3], array_keys(AmoEnums::note_types))){
		die("Pass correct type like: " . implode(', ', array_keys(AmoEnums::note_types)) . "!!\n");
	}

	$entity_id = $argv[2];
	$note_type = AmoEnums::note_types[$argv[3]];
	$amo = new AmoAPI(AMO_AUTH_LOGIN, AMO_AUTH_HASH, AMO_SUBDOMAIN, AMO_COOKIE_FILE, AMO_DEBUG);
	if (!$amo->auth()){
		die("Can't auth!!\n");
	}

	$entity = $amo->get_contact_by_id($entity_id);
	$type = AmoEnums::entity_field_types['contact'];
	if (!$entity){
		$entity = $amo->get_lead_by_id($entity_id);
		$type = AmoEnums::entity_field_types['lead'];
		if (!$entity){
			$entity = $amo->get_company_by_id($entity_id);
			$type = AmoEnums::entity_field_types['company'];
			if (!$entity){
				die("Can't find an entity!!\n");
			}
		}
	}
	die(print_r($amo->create_note($entity_id, $type, $note_type, rand(0, 100)), TRUE));
	break;
case 'add_task':
	if (!isset($argv[2])){
		die("Pass ID of entity!!\n");
	}
	if (!isset($argv[3])){
		die("Pass due date!!\n");
	}
	if (!isset($argv[4])){
		die("Pass text of task!!\n");
	}
	if (!isset($argv[5])){
		die("Pass user_id!!\n");
	}

	$entity_id = $argv[2];
	$date = strtotime($argv[3]);
	if (!$date){
		die("Pass a valid date like '10 September 2000'!!\n");
	}
	

	$text = $argv[4];
	$user_id = $argv[5];

	$amo = new AmoAPI(AMO_AUTH_LOGIN, AMO_AUTH_HASH, AMO_SUBDOMAIN, AMO_COOKIE_FILE, AMO_DEBUG);
	if (!$amo->auth()){
		die("Can't auth!!\n");
	}

	$users = $amo->get_users();
	if (!isset($users[$user_id])){
		die("Pass a existing user ID like " . implode(', ', array_keys($users)) . "!!\n");
	}

	$entity = $amo->get_contact_by_id($entity_id);
	$type = AmoEnums::entity_field_types['contact'];
	if (!$entity){
		$entity = $amo->get_lead_by_id($entity_id);
		$type = AmoEnums::entity_field_types['lead'];
		if (!$entity){
			$entity = $amo->get_company_by_id($entity_id);
			$type = AmoEnums::entity_field_types['company'];
			if (!$entity){
				die("Can't find an entity!!\n");
			}
		}
	}
	die(print_r($amo->create_task($entity_id, $type, 1, $text, (int) $date, $user_id), TRUE));
case 'end_task':
	if (!isset($argv[2])){
		die("Pass ID of task!!\n");
	}
	$task_id = $argv[2];

	$amo = new AmoAPI(AMO_AUTH_LOGIN, AMO_AUTH_HASH, AMO_SUBDOMAIN, AMO_COOKIE_FILE, AMO_DEBUG);
	if (!$amo->auth()){
		die("Can't auth!!\n");
	}
	if(!$amo->get_task_by_id($task_id)){
		die("Not a valid task id!!\n");
	}
	die(print_r($amo->close_task($task_id), TRUE));
}