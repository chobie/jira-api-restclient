<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * @return chobie\Jira\Api
 */
function getApiClient()
{
	$api = new \chobie\Jira\Api(
		'https://your-jira-project.net',
		new \chobie\Jira\Api\Authentication\Basic('yourname', 'password')
	);

	return $api;
}
