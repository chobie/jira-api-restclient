<?php

require __DIR__.'/../vendor/autoload.php';

/**
 * @return \Chobie\JiraApiRestClient\Jira\Api
 */
function getApiClient()
{
    $api = new \Chobie\JiraApiRestClient\Jira\Api(
        'https://your-jira-project.net',
        new \Chobie\JiraApiRestClient\Jira\Api\Authentication\Basic('yourname', 'password')
    );

    return $api;
}
