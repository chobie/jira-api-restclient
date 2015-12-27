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
use chobie\Jira\Api\Result;
use chobie\Jira\Api\Client\CurlClient;
use chobie\Jira\Api\Exception;

class Api
{
    const REQUEST_GET = "GET";
    const REQUEST_POST = "POST";
    const REQUEST_PUT = "PUT";
    const REQUEST_DELETE = "DELETE";


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
     * create a jira api client.
     *
     * @param $endpoint
     * @param AuthenticationInterface $authentication
     * @param ClientInterface|null $client
     */
    public function __construct(
        $endpoint,
        AuthenticationInterface $authentication,
        ClientInterface $client = null
    ) {
        //Regular expression to remove trailing slash
        $endpoint = preg_replace('{/$}', '', $endpoint);
        
        $this->setEndPoint($endpoint);
        $this->authentication = $authentication;

        if (is_null($client)) {
            $client = new CurlClient();
        }

        $this->client = $client;
    }

    /**
     * @param int $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * get endpoint url
     *
     * @return mixed
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * set end point url.
     *
     * @param string $url
     * @return void
     */
    public function setEndPoint($url)
    {
        $this->fields = array();

        $this->endpoint = $url;
    }

    /**
     * get fields definitions.
     *
     * @return array
     */
    public function getFields()
    {
        if (!count($this->fields)) {
            $fields = array();
            $_fields = $this->api(self::REQUEST_GET, "/rest/api/2/field", array());

            /* set hash key as custom field id */
            foreach ($_fields->getResult() as $k => $v) {
                $fields[$v['id']] = $v;
            }
            $this->fields = $fields;
        }

        return $this->fields;
    }

    /**
     * get specified issue.
     *
     * issue key should be YOURPROJ-221
     *
     * @param string $issueKey
     * @param string $expand
     * @return Result|false|mixed
     */
    public function getIssue($issueKey, $expand = '')
    {
        return $this->api(self::REQUEST_GET, sprintf("/rest/api/2/issue/%s", $issueKey), array('expand' => $expand));
    }

    /**
     * @param string $issueKey
     * @param array $params
     * @return Result|false|mixed
     */
    public function editIssue($issueKey, $params)
    {
        return $this->api(self::REQUEST_PUT, sprintf("/rest/api/2/issue/%s", $issueKey), $params);
    }

    /**
     * Delete issue
     *
     * @param string $issueKey should be YOURPROJ-221
     * @param string $deleteSubtasks if all subtask should be deleted
     * @return mixed
     */
    public function deleteIssue($issueKey, $deleteSubtasks = 'true')
    {
        return $this->api(
            self::REQUEST_DELETE, sprintf("/rest/api/2/issue/%s", $issueKey), 
            array (
                'deleteSubtasks' => $deleteSubtasks
                )
        );
    }   

    /**
     * @param string $attachmentId
     * @return Result|false|mixed
     */
    public function getAttachment($attachmentId)
    {
        $result = $this->api(self::REQUEST_GET, "/rest/api/2/attachment/$attachmentId", array(), true);

        return $result;
    }


    /**
     * @param string $expand
     * @return Result|false|mixed
     */
    public function getProjects($expand = '')
    {
        return $this->api(self::REQUEST_GET, "/rest/api/2/project",array('expand' => $expand), true);
    }

    /**
     * @param string $projectKey
     * @param string $expand
     * @return Result|false|mixed
     */
    public function getProject($projectKey, $expand = '')
    {
        $result = $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}", array('expand' => $expand), true);

        return $result;
    }

    /**
     * @param string $projectKey
     * @return Result|false|mixed
     */
    public function getRoles($projectKey)
    {
        $result = $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/roles", array(), true);
        return $result;
    }

    /**
     * @param string $projectKey
     * @param int $roleId
     * @return Result|false|mixed
     */
    public function getRoleDetails($projectKey, $roleId)
    {
        $result = $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/role/{$roleId}", array(), true);
        return $result;
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
     * @return string
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

        if($projectIds !== null)
            $data["projectIds"] = implode(",", $projectIds);
        if($projectKeys !== null)
            $data["projectKeys"] = implode(",", $projectKeys);
        if($issuetypeIds !== null)
            $data["issuetypeIds"] = implode(",", $issuetypeIds);
        if($issuetypeNames !== null)
            $data["issuetypeNames"] = implode(",", $issuetypeNames);
        if($expand !== null)
            $data["expand"] = implode(",", $expand);

        $result = $this->api(self::REQUEST_GET, "/rest/api/2/issue/createmeta", $data, true);
        return $result;
    }


    /**
     * add a comment to a ticket
     *
     * issue key should be YOURPROJ-221
     *
     * @param $issueKey
     * @param $params
     * @return Result|mixed|false
     */
    public function addComment($issueKey, $params)
    {
        if (is_string($params)) {
            // if $params is scalar string value -> wrapping it properly
            $params = array(
                'body' => $params
            );
        }
        return $this->api(self::REQUEST_POST, sprintf("/rest/api/2/issue/%s/comment", $issueKey), $params);
    }
    
    /**
     * get all worklogs for an issue
     *
     * issue key should be YOURPROJ-22
     *
     * @param $issueKey
     * @param $params
     * @return mixed
     */
    public function getWorklogs($issueKey, $params)
    {
        return $this->api(self::REQUEST_GET, sprintf("/rest/api/2/issue/%s/worklog", $issueKey), $params);
    }

    /**
     * get available transitions for a ticket
     *
     * issue key should be YOURPROJ-22
     *
     * @param $issueKey
     * @param $params
     * @return Result|mixed|false
     */
    public function getTransitions($issueKey, $params)
    {
        return $this->api(self::REQUEST_GET, sprintf("/rest/api/2/issue/%s/transitions", $issueKey), $params);
    }



    /**
     * transition a ticket
     *
     * issue key should be YOURPROJ-22
     *
     * @param $issueKey
     * @param $params
     * @return Result|mixed|false
     */
    public function transition($issueKey, $params)
    {
        return $this->api(self::REQUEST_POST, sprintf("/rest/api/2/issue/%s/transitions", $issueKey), $params);
    }

    /**
     * Transition by step name
     *
     * @param string $issueKey like YOURPROJ-22
     * @param string $stepName Step name like 'Done' or 'To Do'
     * @param $params (array of parameters from JIRA API)
     * @return mixed
     */
    public function transitionByStepName($issueKey, $stepName, $params = array())
    {
         $result = array();
        //  get available transitions
        $tmp_transitions = $this->getTransitions($issueKey, array());
        $tmp_transitions_result = $tmp_transitions->getResult();
        $transitions = $tmp_transitions_result['transitions'];

        //  search id for closing ticket
        foreach ($transitions as $v) {
            //  Close ticket if required id was found
            if ($v['name'] == $stepName) {
                $result = $this->transition(
                    $issueKey,
                    array_merge($params,
                        array(
                            'transition' => array(
                            'id' => $v['id']
                            )
                        )
                    )
                );
                break;
            }
        }
        return $result;
    }

    /**
     * get available issue types
     *
     * @return IssueType[]
     */
    public function getIssueTypes()
    {
        $result = array();
        $types = $this->api(self::REQUEST_GET, "/rest/api/2/issuetype", array(), true);

        foreach ($types as $issue_type) {
            $result[] = new IssueType($issue_type);
        }

        return $result;
    }

    /**
     * get available versions
     *
     * @param string $projectKey
     * @return mixed
     */
    public function getVersions($projectKey)
    {
        $result = $this->api(self::REQUEST_GET, "/rest/api/2/project/{$projectKey}/versions", array(), true);
        return $result;
    }

    /**
     * For backwards compatibility
     *
     * @deprecated use getPriorities() instead
     * @return mixed
     */
    public function getPriorties()
    {
        return $this->getPriorities();
    }

    /**
     * get available priorities
     *
     * @return mixed
     */
    public function getPriorities()
    {
        if (!count($this->priorities)) {
            $priorities = array();
            $result = $this->api(self::REQUEST_GET, "/rest/api/2/priority", array());
            /* set hash key as custom field id */
            foreach ($result->getResult() as $k => $v) {
                $priorities[$v['id']] = $v;
            }
            $this->priorities = $priorities;
        }
        return $this->priorities;
    }

    /**
     * get available statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        if (!count($this->statuses)) {
            $statuses = array();
            $result = $this->api(self::REQUEST_GET, "/rest/api/2/status", array());
            /* set hash key as custom field id */
            foreach ($result->getResult() as $k => $v) {
                $statuses[$v['id']] = $v;
            }
            $this->statuses = $statuses;
        }
        return $this->statuses;
    }


    /**
     * create an issue.
     *
     * @param $projectKey
     * @param $summary
     * @param $issueType
     * @param array $options
     * @return Result|mixed|false
     */
    public function createIssue($projectKey, $summary, $issueType, $options = array())
    {
        $default = array(
            "project" => array(
                "key" => $projectKey,
            ),
            "summary" => $summary,
            "issuetype" => array(
                "id" => $issueType,
            )
        );

        $default = array_merge($default, $options);

        $result = $this->api(
            self::REQUEST_POST,
            "/rest/api/2/issue/",
            array(
                "fields" => $default
            )
        );

        return $result;
    }

    /**
     * query issues
     *
     * @param $jql
     * @param $startAt
     * @param $maxResult
     * @param string $fields
     * @return Result|mixed|false
     */
    public function search($jql, $startAt = 0, $maxResult = 20, $fields = '*navigable')
    {
        $result = $this->api(
            self::REQUEST_GET,
            "/rest/api/2/search",
            array(
                "jql" => $jql,
                "startAt" => $startAt,
                "maxResults" => $maxResult,
                "fields" => $fields,
            )
        );

        return $result;
    }

    /**
     * create JIRA Version
     *
     * @param $project_id
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function createVersion($project_id, $name, $options = array())
    {
        $options = array_merge(
            array(
                "name" => $name,
                "description" => "",
                "project" => $project_id,
                //"userReleaseDate" => "",
                //"releaseDate"     => "",
                "released" => false,
                "archived" => false,
            ),
            $options
        );

        return $this->api(self::REQUEST_POST, "/rest/api/2/version", $options);
    }


    /**
     * create JIRA Attachment
     *
     * @param string $issue Jira Issue key
     * @param string $filename Path to file
     * @param array $options
     * @return mixed
     */
    public function createAttachment($issue, $filename, $options = array())
    {
        $options = array_merge(
            array(
                "file" => '@' . $filename . ';filename=' . pathinfo($filename, PATHINFO_BASENAME)
            ),
            $options
        );

        return $this->api(self::REQUEST_POST, "/rest/api/2/issue/" . $issue . "/attachments", $options, false, true);
    }

    /**
     * Create a remote link
     *
     * @param $issue
     * @param array $object
     * @param string $relationship
     * @param string $globalid
     * @param array $application
     * @return mixed
     */
    public function createRemotelink(
            $issue,
            $object = array(),
            $relationship = null,
            $globalid = null,
            $application = null
    ) {
        $options = array(
                        "globalid" => $globalid,
                        "relationship" => $relationship,
                        "object" => $object
                    );

        if (!is_null($application)) {
            $options['application'] = $application;
        }

        return $this->api(self::REQUEST_POST,
                            "/rest/api/2/issue/" . $issue . "/remotelink",
                            $options, true);
    }

    /**
     * send request to specified host
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param bool $return_as_json
     * @param bool $isfile
     * @param bool $debug
     * @return Result|false|mixed
     * @throws Exception
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

        if (strlen($result)) {
            $json = json_decode($result, true);
            if (!is_array($json)) {
                 throw new Exception("JIRA Rest server returns unexpected result: " . $result);
            }

            if ($this->options & self::AUTOMAP_FIELDS) {
                if (isset($json['issues'])) {
                    if (!count($this->fields)) {
                        $this->getFields();
                    }

                    foreach ($json['issues'] as $offset => $issue) {
                        $json['issues'][$offset] = $this->automapFields($issue);
                    }
                }

            }

            if ($return_as_json) {
                return $json;
            } else {
                return new Result($json);
            }
        } else {
            return false;
        }
    }

    public function downloadAttachment($url)
    {
        $result = $this->client->sendRequest
            (
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

    protected function automapFields($issue)
    {
        if (isset($issue['fields'])) {
            $x = array();
            foreach ($issue['fields'] as $kk => $vv) {
                if (isset($this->fields[$kk])) {
                    $x[$this->fields[$kk]['name']] = $vv;
                } else {
                    $x[$kk] = $vv;
                }
            }
            $issue['fields'] = $x;
        }

        return $issue;
    }

    /**
     * set watchers in a ticket
     *
     * @param $issueKey
     * @param $watchers
     * @return mixed
     */
    public function setWatchers($issueKey, $watchers)
    {
        $result = array();
        foreach ($watchers as $w) {
            $result[] = $this->api(self::REQUEST_POST, sprintf("/rest/api/2/issue/%s/watchers", $issueKey), $w);
        }
        return $result;
    }

    /**
     * close issue
     *
     * @param $issueKey
     * @return mixed
     *
     */
    public function closeIssue($issueKey)
    {
        return $this->transitionByStepName($issueKey,'Close Issue');
    }
}
