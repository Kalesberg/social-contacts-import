<?php
session_start();
require_once '../vendor/autoload.php';

function prettify($contact) {
	$newElem = new stdClass;
	$newElem->name = $contact['name'];
	$newElem->source = 'facebook';
	$newElem->sourceContactId = $contact['id'];
	$newElem->photo = $contact['picture']['url'];
	
	return $newElem;
}
function importContacts($contacts) {
	$contacts = array_map('prettify', $contacts);

	$printableJSON = json_encode($contacts, JSON_PRETTY_PRINT);
	header("Content-disposition: attachment; filename=facebook.json");
	header("Content-type: application/json");
	echo $printableJSON;
	exit;
}

$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);
$config = (array) json_decode($contents);

$fb = new Facebook\Facebook($config);
if(isset($_SESSION['facebook.token'])) {
	$fb->setDefaultAccessToken($_SESSION['facebook.token']);

	try {
	  $response = $fb->get('/me');
	  $userNode = $response->getGraphUser();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	  // When Graph returns an error
	  echo 'Graph returned an error: ' . $e->getMessage();
	  exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	  // When validation fails or other local issues
	  echo 'Facebook SDK returned an error: ' . $e->getMessage();
	  exit;
	}

	// $response = $fb->get('/me/friendlists');
	// $friends = $response->getGraphEdge();
	
	$response = $fb->get('/me/taggable_friends');
	$friends = $response->getGraphEdge();
	if ($fb->next($friends)) {
		$allFriends = array();
		$friendsArray = $friends->asArray();
		$allFriends = array_merge($friendsArray, $allFriends);
		while ($friends = $fb->next($friends)) {
			$friendsArray = $friends->asArray();
			$allFriends = array_merge($friendsArray, $allFriends);
		}
	} else {
		$allFriends = $friends->asArray();
	}
	importContacts($allFriends);
}
else {
	$helper = $fb->getRedirectLoginHelper();
	$permissions = ['user_friends'];
	$loginURL = $helper->getLoginUrl('https://contact-import-123.herokuapp.com/facebook/fbredirect.php', $permissions);

	header("Location: " . $loginURL);
}