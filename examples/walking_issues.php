<?php
require dirname(__FILE__) . '/common.php';

$api = getApiClient();
/**
 * Jira_Issues_Walker implicitly paging search request.
 * you don't need to care about paging request
 *
 * push(string $jql, string $navigable = null, array $expanded = array())
 *
 * `push` function calls Jira_Api::search($jql, $startAt = 0, $maxResult = 20, $fields = '*navigable', $expanded = array()) internally.
 *
 * @see https://developer.atlassian.com/static/rest/jira/5.0.html#id202584
 */
$walker = new \chobie\Jira\Issues\Walker($api);
$walker->push('project = TICKETACEG AND  updated > -1d ORDER BY priority DESC', '*navigable');

// Okay, then just do foreach walker variable to pull issues.
foreach ( $walker as $issue ) {
	var_dump($issue);
}

/**
 * You can also set fieldsets to be expanded
 *
 * push(string $jql, string $navigable = null, array $expanded = array())
 */

$walker->push(
    'project = TICKETACEG AND  updated > -1d ORDER BY priority DESC',
    null,
    array('history','changelog')
);
foreach ( $walker as $issue ) {
    var_dump($issue);
}

/**
 * You can set the paging size when constructing the Walker by passing a second parameter
 * e.g. $walker = new Walker($api, 50);
 */
