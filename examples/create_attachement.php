<?php
require dirname(__FILE__) . '/common.php';

$api = getApiClient();
$api->createAttachment('TEST-1', 'droid.png');
