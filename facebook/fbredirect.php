<?php
session_start();
require_once '../vendor/autoload.php';

$configPath = '.config.json';
if(!file_exists($configPath))
	throw new \Exception('Not found .config.json');
$contents = file_get_contents($configPath);
$config = (array) json_decode($contents);

$fb = new Facebook\Facebook($config);
$helper = $fb->getRedirectLoginHelper();

try {
	$accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}

if (isset($accessToken)) {
	// Logged in!
	$_SESSION['facebook.token'] = (string) $accessToken;
	die('<script type="text/javascript">window.location.href="index.php";</script>');
	// Now you can redirect to another page and use the
	// access token from $_SESSION['facebook.token']

}