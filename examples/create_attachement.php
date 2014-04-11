<?php
require dirname(__FILE__) ."/common.php";

$api = new Jira_Api(
    "https://your-jira-project.net",
    new Jira_Api_Authentication_Basic("yourname", "password")
);

$api->createAttachment("TEST-1", "droid.png");
