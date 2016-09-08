<?php
require dirname(__FILE__) . '/common.php';

$api = getApiClient();

$update_object = new stdClass();
$update_object->customfield_10401 = array(
	array('set' => 'Value here'),
);

$r = $api->editIssue($key, array(
	'update' => $update_object,
));

/*
$api->editIssue($key, array(
	'fields' => array(
		'<FieldID>' => 'Value',
	),
));
*/
