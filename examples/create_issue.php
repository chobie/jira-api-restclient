<?php
require dirname(__FILE__) ."/common.php";

$api = getApiClient();
$api->createIssue('<Projet Key>', '<Summary>', '<Task Type Id>', array(
	'description' => '<Issue Description>'
));
