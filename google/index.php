<?php
session_start();
require_once '../vendor/autoload.php';
require_once 'GoogleHelper.php';
require_once 'ContactFactory.php';

use rapidweb\googlecontacts\objects\Contact;

function prettify($element) {
	$emails = array();
	foreach($element->email as $mail)
		$emails[] = $mail['email'];
	if(count($emails) == 1)
		$emails = $emails[0];
	$element->email = $emails;
	$element->source = 'google';
	$element->sourceContactId = $element->id;
	$element->apiURL = $element->selfURL;
	
	unset($element->id);
	unset($element->selfURL);
	unset($element->editURL);
	
	return $element;
}
function importContacts() {
	$contacts = ContactFactory::getAll();
	$contacts = array_map('prettify', $contacts);

	$printableJSON = '';
	if($contacts)
		$printableJSON = json_encode($contacts, JSON_PRETTY_PRINT);
	header("Content-disposition: attachment; filename=google.json");
	header("Content-type: application/json");
	echo $printableJSON;
	exit;
}

if(!isset($_SESSION['google.token']) || !$_SESSION['google.token']) {
	if(isset($_GET['code'])) {
		$client = GoogleHelper::getClient();
		GoogleHelper::authenticate($client, $_GET['code']);
		$accessToken = GoogleHelper::getAccessToken($client);
		if (!isset($accessToken->refresh_token)) {
			var_dump($accessToken);
			die('Invalid access token! Try again later.');
		}
		$_SESSION['google.token'] = $accessToken->refresh_token;
		die('<script type="text/javascript">window.location.href="index.php";</script>');
		// importContacts();
	}
	else {
		$client = GoogleHelper::getClient();
		$authURL = GoogleHelper::getAuthUrl($client);
		header("Location: " . $authURL);
	}
}
else
	importContacts();

exit;
?>