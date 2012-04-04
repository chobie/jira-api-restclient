<?php
/*
 * The MIT License
 *
 * Copyright (c) 2012 Shuhei Tanuma
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
namespace Jira\Api;

class RestClient{
    protected $user;
    protected $password;
    
    protected $endpoint;
    
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }
    
    public function getUserName()
    {
        return $this->user;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function getEndpoint()
    {
        return $this->endpoint;
    }
    
    public function setEndPoint($url)
    {
        $this->endpoint = $url;
    }
    
    public function getIssue($issueKey)
    {
        return $this->api("GET","/rest/api/2/issue/{$issueKey}");
    }
    
    public function getIssueTypes()
    {
        return $this->api("GET","/rest/api/2/issuetype");
    }
    
    public function createIssue($projectId, $summary, $description, $issueType, $options = array())
    {
        $default = array(
            "project" => array(
                "id"  => $projectId,
            ),
            "summary"     => $summary,
            "description" => $description,
            "issuetype"   => array(
                "id" => $issueType,
        ));
        $default = array_merge($default, $options);
        $result = $this->api("POST","/rest/api/2/issue/",array(
            "fields" => $default
        ));
        /*
            $result["id"];
            $result["key"];
            $result["self"];
        */
        return $result;
    }
    
    public function api($method="GET", $url, $data = array())
    {
        $data = json_encode($data);

        $header = array(
            "Authorization: Basic " . base64_encode($this->getUserName() . ":" . $this->getPassword()),
        );
        $header[] = 'Content-Type: application/json';

        if ($method=="POST") {
            $header[] = 'Content-Length: ' . strlen($data);
        }
        
        $context = array(
            "http" => array(
                "method"  => $method,
                "header"  => join("\n", $header),
        ));
        $context['http']['content'] = $data;
        
        $data = file_get_contents($this->getEndpoint() . $url, 
            false, 
            stream_context_create($context)
        );
        
        return json_decode($data,true);
    }
}
