<?php
require_once '../vendor/autoload.php';
require_once 'GoogleHelper.php';

$client = GoogleHelper::getClient();
$authUrl = GoogleHelper::getAuthUrl($client);
var_dump($authUrl);die;

echo 'Go to the following URL to authorise your application for Google Contacts: '.$authUrl;
echo "\r\n";