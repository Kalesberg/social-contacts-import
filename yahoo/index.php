<?php
require("Yahoo.inc");

function prettify($element) {
	$newElem = new stdClass;
	$fields = $element->fields;
	
	foreach($fields as $field) {
		if(is_object($field->value)) {
			if($field->type == 'name') {
				$newElem->givenName = $field->value->givenName;
				$newElem->middleName = $field->value->middleName;
				$newElem->familyName = $field->value->familyName;
			} else {
				$key = $field->type;
				$value = current($field->value);
				$newElem->$key = $value;
			}
		}
		else {
			$key = $field->type;
			$value = $field->value;
			$newElem->$key = $value;
		}
	}
	$newElem->source = 'yahoo';
	$newElem->apiURL = $field->uri;
	
	return $newElem;
}
function importContacts($contacts) {
	$contacts = $contacts->contacts->contact;
	$contacts = array_map('prettify', $contacts);
	$printableJSON = json_encode($contacts, JSON_PRETTY_PRINT);
	header("Content-disposition: attachment; filename=yahoo.json");
	header("Content-type: application/json");
	echo $printableJSON;
	exit;
}

$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);
$config = (array) json_decode($contents);

$session = YahooSession::requireSession($config['ConsumerKey'],$config['ConsumerSecret'],$config['AppID']);

// Fetch the logged-in (sessioned) user  
$user = $session->getSessionedUser();  

// Fetch the profile for the current user.  
// $profile = $user->getProfile();  

$contacts = $user->getContacts();
importContacts($contacts);
exit;
?>
