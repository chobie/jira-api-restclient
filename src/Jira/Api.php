<?php
/*
 * The MIT License
 *
 * Copyright (c) 2014 Shuhei Tanuma
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace chobie\Jira;


use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Client\ClientInterface;
use chobie\Jira\Api\Client\CurlClient;
use chobie\Jira\Api\Result;

class Api
{
	const REQUEST_GET = 'GET';
	const REQUEST_POST = 'POST';
	const REQUEST_PUT = 'PUT';
	const REQUEST_DELETE = 'DELETE';

	const AUTOMAP_FIELDS = 0x01;

	/** @var string $endpoint */
	protected $endpoint;

	/** @var ClientInterface */
	protected $client;

	/** @var AuthenticationInterface */
	protected $authentication;

	/** @var int $options */
	protected $options = self::AUTOMAP_FIELDS;

	/** @var array $fields */
	protected $fields;

	/** @var array $priority */
	protected $priorities;

	/** @var array $status */
	protected $statuses;

	/**
	 * Create a JIRA API client.
	 *
	 * @param string                  $endpoint       URL
	 * @param AuthenticationInterface $authentication
	 * @param ClientInterface         $client
	 */
	public function __construct(
		$endpoint,
		AuthenticationInterface $authentication,
		ClientInterface $client = null
	) {
		$this->setEndPoint($endpoint);
		$this->authentication = $authentication;

		if ( is_null($client) ) {
			$client = new CurlClient();
		}

		$this->client = $client;
	}

	/**
	 * @param integer $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}

	/**
	 * Get Endpoint URL
	 *
	 * @return string
	 */
	public function getEndpoint()
	{
		return $this->endpoint;
	}

	/**
	 * Set Endpoint URL
	 *
	 * @param string $url
	 */
	public function setEndpoint($url)
	{
		$this->fields = array();

		// Remove trailing slash in the url
		$url = rtrim($url, '/');

		$this->endpoint = $url;
	}

	/**
	 * Get fields definitions.
	 *
	 * @return array
	 */
	public function getFields()
	{
		if ( !count($this->fields) ) {
			$fields = array();
			$_fields = $this->api(self::REQUEST_GET, '/rest/api/2/field', array());

			/* set hash key as custom field id */
			foreach ( $_fields->getResult() as $k => $v ) {
				$fields[$v['id']] = $v;
			}

			$this->fields = $fields;
		}

		return $this->fields;
	}

	/**
	 * Get specified issue.
	 *
	 * @param string $issueKey Issue key should be YOURPROJ-221
	 * @param string $expand
	 *
	 * @return Result|false
	 */
	public function getIssue($issueKey, $expand = '')
	{
		return $this->api(self::REQUEST_GET, sprintf('/rest/api/2/issue/%s', $issueKey), array('expand' => $expand));
	}

	/**
	 * @param string $issueKey
	 * @param array  $params
	 *
	 * @return Result|false
	 */
	public function editIssue($issueKey, $params)
	{
		return $this->api(self::REQUEST_PUT, sprintf('/rest/api/2/issue/%s', $issueKey), $params);
	}

	/**
	 * @param string $attachmentId
	 *
	 * @return array|false
	 */
	public function getAttachment($attachmentId)
	{
		return $this->api(self::REQUEST_GET, '/rest/api/2/attachment/' . $attachmentId, array(), true);
	}

	/**
	 * @return Result|false
	 */
	public function getProjects()
	{
		return $this->api(self::REQUEST_GET, '/rest/api/2/project');
	}

	/**
	 * @param string $projectKey
	 *
	 * @return array|false
	 */
	public function getProject($projectKey)
	{
		return $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}", array(), true);
	}

	/**
	 * @param string $projectKey
	 *
	 * @return array|false
	 */
	public function getRoles($projectKey)
	{
		return $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/roles", array(), true);
	}

	/**
	 * @param string $projectKey
	 * @param string $roleId
	 *
	 * @return array|false
	 */
	public function getRoleDetails($projectKey, $roleId)
	{
		return $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/role/{$roleId}", array(), true);
	}

	/**
	 * Returns the meta data for creating issues. This includes the available projects, issue types
	 * and fields, including field types and whether or not those fields are required.
	 * Projects will not be returned if the user does not have permission to create issues in that project.
	 * Fields will only be returned if "projects.issuetypes.fields" is added as expand parameter.
	 *
	 * @param $projectIds array      Combined with the projectKeys param, lists the projects with which to filter the results.
	 *                               If absent, all projects are returned. Specifiying a project that does not exist (or that
	 *                               you cannot create issues in) is not an error, but it will not be in the results.
	 * @param $projectKeys array     Combined with the projectIds param, lists the projects with which to filter the
	 *                               results. If null, all projects are returned. Specifiying a project that does not exist (or
	 *                               that you cannot create issues in) is not an error, but it will not be in the results.
	 * @param $issuetypeIds array    Combined with issuetypeNames, lists the issue types with which to filter the results.
	 *                               If null, all issue types are returned. Specifiying an issue type that does not exist is
	 *                               not an error.

	 * @param $issuetypeNames array  Combined with issuetypeIds, lists the issue types with which to filter the results.
	 *                               If null, all issue types are returned. This parameter can be specified multiple times,
	 *                               but is NOT interpreted as a comma-separated list. Specifiying an issue type that does
	 *                               not exist is not an error.
	 * @param $expand array          Optional list of entities to expand in the response.
	 *
	 * @return array|false
	 */
	public function getCreateMeta(
		array $projectIds = null,
		array $projectKeys = null,
		array $issuetypeIds = null,
		array $issuetypeNames = null,
		array $expand = null
	) {
		// Create comma separated query parameters for the supplied filters
		$data = array();

		if( $projectIds !== null ) {
			$data['projectIds'] = implode(',', $projectIds);
		}

		if( $projectKeys !== null ) {
			$data['projectKeys'] = implode(',', $projectKeys);
		}

		if( $issuetypeIds !== null ) {
			$data['issuetypeIds'] = implode(',', $issuetypeIds);
		}

		if( $issuetypeNames !== null ) {
			$data['issuetypeNames'] = implode(',', $issuetypeNames);
		}

		if( $expand !== null ) {
			$data['expand'] = implode(',', $expand);
		}

		return $this->api(self::REQUEST_GET, '/rest/api/2/issue/createmeta', $data, true);
	}

	/**
	 * Add a comment to a ticket
	 *
	 * @param string $issueKey Issue key should be YOURPROJ-221
	 * @param array  $params
	 *
	 * @return Result|false
	 */
	public function addComment($issueKey, $params)
	{
		if ( is_string($params) ) {
			// if $params is scalar string value -> wrapping it properly
			$params = array(
				'body' => $params,
			);
		}

		return $this->api(self::REQUEST_POST, sprintf('/rest/api/2/issue/%s/comment', $issueKey), $params);
	}

	/**
	 * Get all worklogs for an issue
	 *
	 * @param $issueKey Issue key should be YOURPROJ-22
	 * @param $params
	 *
	 * @return Result|false
	 */
	public function getWorklogs($issueKey, $params)
	{
		return $this->api(self::REQUEST_GET, sprintf('/rest/api/2/issue/%s/worklog', $issueKey), $params);
	}

	/**
	 * Get available transitions for a ticket
	 *
	 * @param string $issueKey Issue key should be YOURPROJ-22
	 * @param array  $params
	 *
	 * @return Result|false
	 */
	public function getTransitions($issueKey, $params)
	{
		return $this->api(self::REQUEST_GET, sprintf('/rest/api/2/issue/%s/transitions', $issueKey), $params);
	}

	/**
	 * Transition a ticket
	 *
	 * @param string $issueKey Issue key should be YOURPROJ-22
	 * @param array  $params
	 *
	 * @return Result|false
	 */
	public function transition($issueKey, $params)
	{
		return $this->api(self::REQUEST_POST, sprintf('/rest/api/2/issue/%s/transitions', $issueKey), $params);
	}

	/**
	 * Get available issue types
	 *
	 * @return IssueType[]
	 */
	public function getIssueTypes()
	{
		$result = array();
		$types = $this->api(self::REQUEST_GET, '/rest/api/2/issuetype', array(), true);

		foreach ( $types as $issue_type ) {
			$result[] = new IssueType($issue_type);
		}

		return $result;
	}

	/**
	 * Get available versions
	 *
	 * @param string $projectKey
	 *
	 * @return array|false
	 */
	public function getVersions($projectKey)
	{
		return $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/versions", array(), true);
	}

	/**
	 * Helper method to find a specific version based on the name of the version.
	 *
	 * @param string $projectKey Project Key
	 * @param string $name       The version name to match on
	 *
	 * @return integer|null VersionId on match or null when there is no match
	 */
	public function findVersionByName($projectKey, $name)
	{
		// Fetch all versions of this project
		$versions = $this->getVersions($projectKey);

		// Filter results on the name
		$matching_versions = array_filter($versions, function (array $version) use ($name) {
			return $version['name'] == $name;
		});

		// Early out for no results
		if ( empty($matching_versions) ) {
			return null;
		}

		// Multiple results should not happen since name is unique
		return reset($matching_versions);
	}

	/**
	 * Get available priorities
	 *
	 * @return array
	 */
	public function getPriorities()
	{
		if ( !count($this->priorities) ) {
			$priorities = array();
			$result = $this->api(self::REQUEST_GET, '/rest/api/2/priority', array());

			/* set hash key as custom field id */
			foreach ( $result->getResult() as $k => $v ) {
				$priorities[$v['id']] = $v;
			}

			$this->priorities = $priorities;
		}

		return $this->priorities;
	}

	/**
	 * Get available priorities
	 *
	 * @return     array
	 * @deprecated Please use getPriorities()
	 */
	public function getPriorties()
	{
		trigger_error('Use getPriorities() instead', E_USER_DEPRECATED);

		return $this->getPriorities();
	}

	/**
	 * Get available statuses
	 *
	 * @return array
	 */
	public function getStatuses()
	{
		if ( !count($this->statuses) ) {
			$statuses = array();
			$result = $this->api(self::REQUEST_GET, '/rest/api/2/status', array());

			/* set hash key as custom field id */
			foreach ( $result->getResult() as $k => $v ) {
				$statuses[$v['id']] = $v;
			}

			$this->statuses = $statuses;
		}

		return $this->statuses;
	}

	/**
	 * Create an issue.
	 *
	 * @param string $projectKey
	 * @param string $summary
	 * @param string $issueType
	 * @param array  $options
	 *
	 * @return Result|false
	 */
	public function createIssue($projectKey, $summary, $issueType, $options = array())
	{
		$default = array(
			'project' => array(
				'key' => $projectKey,
			),
			'summary' => $summary,
			'issuetype' => array(
				'id' => $issueType,
			),
		);

		$default = array_merge($default, $options);

		$result = $this->api(
			self::REQUEST_POST,
			'/rest/api/2/issue/',
			array(
				'fields' => $default,
			)
		);

		return $result;
	}

	/**
	 * Query issues
	 *
	 * @param string  $jql
	 * @param integer $startAt
	 * @param integer $maxResult
	 * @param string  $fields
	 *
	 * @return Result|false
	 */
	public function search($jql, $startAt = 0, $maxResult = 20, $fields = '*navigable')
	{
		$result = $this->api(
			self::REQUEST_GET,
			'/rest/api/2/search',
			array(
				'jql' => $jql,
				'startAt' => $startAt,
				'maxResults' => $maxResult,
				'fields' => $fields,
			)
		);

		return $result;
	}

	/**
	 * Create JIRA Version
	 *
	 * @param string $projectId
	 * @param string $name
	 * @param array  $options
	 *
	 * @return Result|false
	 */
	public function createVersion($projectId, $name, $options = array())
	{
		$options = array_merge(
			array(
				'name' => $name,
				'description' => '',
				'project' => $projectId,
				// "userReleaseDate" => "",
				// "releaseDate"     => "",
				'released' => false,
				'archived' => false,
			),
			$options
		);

		return $this->api(self::REQUEST_POST, '/rest/api/2/version', $options);
	}

	/**
	 * Update JIRA Version
	 *
	 * https://docs.atlassian.com/jira/REST/latest/#api/2/version-updateVersion
	 *
	 * @param integer $versionId Version identifier
	 * @param array   $params    Key->Value list to update the version with.
	 *
	 * @return Result|false
	 */
	public function updateVersion($versionId, $params = array())
	{
		return $this->api(self::REQUEST_PUT, sprintf('/rest/api/2/version/%d', $versionId), $params);
	}

	/**
	 * Shorthand to mark a version as Released
	 *
	 * @param integer     $versionId   Version identifier
	 * @param string|null $releaseDate Date in Y-m-d format. Defaults to today
	 * @param array       $params      Optionally extra parameters.
	 *
	 * @return Result|false
	 */
	public function releaseVersion($versionId, $releaseDate = null, $params = array())
	{
		if( !$releaseDate ) {
			$releaseDate = date('Y-m-d');
		}

		$params = array_merge(
			array(
				'releaseDate' => $releaseDate,
				'released' => true,
			),
			$params
		);

		return $this->updateVersion($versionId, $params);
	}

	/**
	 * Create JIRA Attachment
	 *
	 * @param string $issueKey
	 * @param string $filename
	 * @param array  $options
	 *
	 * @return Result|false
	 */
	public function createAttachment($issueKey, $filename, $options = array())
	{
		$options = array_merge(
			array(
				'file' => '@' . $filename,
			),
			$options
		);
		return $this->api(self::REQUEST_POST, '/rest/api/2/issue/' . $issueKey . '/attachments', $options, false, true);
	}

	/**
	 * Create a remote link
	 *
	 * @param string $issueKey
	 * @param array  $object
	 * @param string $relationship
	 * @param string $globalid
	 * @param array  $application
	 *
	 * @return array|false
	 */
	public function createRemotelink(
		$issueKey,
		$object = array(),
		$relationship = null,
		$globalid = null,
		$application = null
	) {
		$options = array(
						'globalid' => $globalid,
						'relationship' => $relationship,
						'object' => $object,
					);

		if ( !is_null($application) ) {
			$options['application'] = $application;
		}

		return $this->api(self::REQUEST_POST, '/rest/api/2/issue/' . $issueKey . '/remotelink', $options, true);
	}

	/**
	 * Send request to specified host
	 *
	 * @param string  $method
	 * @param string  $url
	 * @param array   $data
	 * @param boolean $return_as_json
	 * @param boolean $isfile
	 * @param boolean $debug
	 *
	 * @return array|Result|false
	 */
	public function api(
		$method = self::REQUEST_GET,
		$url,
		$data = array(),
		$return_as_json = false,
		$isfile = false,
		$debug = false
	) {
		$result = $this->client->sendRequest(
			$method,
			$url,
			$data,
			$this->getEndpoint(),
			$this->authentication,
			$isfile,
			$debug
		);

		if ( strlen($result) ) {
			$json = json_decode($result, true);

			if ( $this->options & self::AUTOMAP_FIELDS ) {
				if ( isset($json['issues']) ) {
					if ( !count($this->fields) ) {
						$this->getFields();
					}

					foreach ( $json['issues'] as $offset => $issue ) {
						$json['issues'][$offset] = $this->automapFields($issue);
					}
				}
			}

			if ( $return_as_json ) {
				return $json;
			}
			else {
				return new Result($json);
			}
		}
		else {
			return false;
		}
	}

	/**
	 * @param string $url
	 *
	 * @return array|string
	 */
	public function downloadAttachment($url)
	{
		$result = $this->client->sendRequest(
			self::REQUEST_GET,
			$url,
			array(),
			null,
			$this->authentication,
			true,
			false
		);

		return $result;
	}

	/**
	 * @param array $issue
	 *
	 * @return array
	 */
	protected function automapFields($issue)
	{
		if ( isset($issue['fields']) ) {
			$x = array();

			foreach ( $issue['fields'] as $kk => $vv ) {
				if ( isset($this->fields[$kk]) ) {
					$x[$this->fields[$kk]['name']] = $vv;
				}
				else {
					$x[$kk] = $vv;
				}
			}

			$issue['fields'] = $x;
		}

		return $issue;
	}

	/**
	 * Set watchers in a ticket
	 *
	 * @param string $issueKey
	 * @param array  $watchers
	 *
	 * @return Result|false
	 */
	public function setWatchers($issueKey, $watchers)
	{
		$result = array();

		foreach ( $watchers as $w ) {
			$result[] = $this->api(self::REQUEST_POST, sprintf('/rest/api/2/issue/%s/watchers', $issueKey), $w);
		}

		return $result;
	}

	/**
	 * Close issue
	 *
	 * @param $issueKey
	 *
	 * @return Result|array
	 * @TODO:  Should have parameters? (e.g comment)
	 */
	public function closeIssue($issueKey)
	{
		$result = array();
		// Get available transitions
		$tmp_transitions = $this->getTransitions($issueKey, array());
		$tmp_transitions_result = $tmp_transitions->getResult();
		$transitions = $tmp_transitions_result['transitions'];

		// Search id for closing ticket
		foreach ( $transitions as $v ) {
			// Close ticket if required id was found
			if ( $v['name'] == 'Close Issue' ) {
				$result = $this->transition(
					$issueKey,
					array(
						'transition' => array(
							'id' => $v['id'],
						),
					)
				);
				break;
			}
		}

		return $result;
	}

}
