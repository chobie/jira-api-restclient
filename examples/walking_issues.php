<?php

require dirname(__FILE__).'/common.php';

$api = getApiClient();
/*
 * Jira_Issues_Walker implicitly paging search request.
 * you don't need to care about paging request
 *
 * push(string $jql, string $navigable)
 *
 * `push` function calls Jira_Api::search($jql, $startAt = 0, $maxResult = 20, $fields = '*navigable') internally.
 *
 * @see
 * https://developer.atlassian.com/static/rest/jira/5.0.html#id202584
 */
$walker = new \Chobie\JiraApiRestClient\Jira\Issues\Walker($api);
$walker->push('project = TICKETACEG AND  updated > -1d ORDER BY priority DESC', '*navigable');

/* okay, then just do foreach walker variable to pull issues */
foreach ($walker as $k => $issue) {
    var_dump($issue);
}
