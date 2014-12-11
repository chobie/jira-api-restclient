<?php
require dirname(__FILE__) ."/common.php";

$api = getApiClient();

$updObj = new stdClass();
$updObj->customfield_10401 = [
    ['set' => 'Value here']
];

$r = $api->editIssue($key, [
    "update" => $updObj
]);

/*        
$api->editIssue($key, array(
    "fields" => array(
        "<FieldID>" => "Value"
    ),
));
*/
