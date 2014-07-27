<?php
require dirname(__FILE__) ."/common.php";

$api = getApiClient();
$api->editIssue($key, array(
    "fields" => array(
        "<FieldID>" => "Value"
    ),
));
