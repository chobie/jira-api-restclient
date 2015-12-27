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
namespace chobie\Jira\Api\Client;

use chobie\Jira\Api\Authentication\Anonymous;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Authentication\Basic;

class PHPClient implements ClientInterface
{
    protected $https_support = false;

    /**
     * create a traditional php client
     */
    public function __construct()
    {
        $wrappers = stream_get_wrappers();
        if (in_array("https", $wrappers)) {
            $this->https_support = true;
        }

    }

    protected function isSupportHttps()
    {
        return $this->https_support;
    }

    /**
     * send request to the api server
     *
     * @param $method
     * @param $url
     * @param array $data
     * @param $endpoint
     * @param AuthenticationInterface $credential
     * @param bool $isFile
     * @param bool $debug
     * @return array|string
     * @throws \Exception
     */
    public function sendRequest(
        $method,
        $url,
        $data = array(),
        $endpoint,
        AuthenticationInterface $credential,
        $isFile = false,
        $debug = false
    ) {
        if (!($credential instanceof Basic) && !($credential instanceof Anonymous)) {
            throw new \Exception(sprintf("PHPClient does not support %s authentication.", get_class($credential)));
        }

        $header = array();
        if (!($credential instanceof Anonymous)) {
            $header[] = "Authorization: Basic " . $credential->getCredential();
        }

        $context = array(
            "http" => array(
                "method" => $method,
                "header" => join("\r\n", $header),
            )
        );


        if (!$isFile) {
            $header[] = "Content-Type: application/json;charset=UTF-8";
        }

        if ($method == "POST" || $method == "PUT") {
            if ($isFile) {
                $filename = preg_replace("/^@/", "", $data['file']);
                $boundary = '--------------------------' . microtime(true);
                $header[] = "X-Atlassian-Token: nocheck";
                $header[] = 'Content-Type: multipart/form-data; boundary=' . $boundary;

                $__data = "--" . $boundary . "\r\n" .
                    "Content-Disposition: form-data; name=\"file\"; filename=\"" . $filename . "\"\r\n" .
                    "Content-Type: application/octet-stream\r\n\r\n" .
                    file_get_contents($filename) . "\r\n";
                $__data .= "--" . $boundary . "--\r\n";
            } else {
                $__data = json_encode($data);
            }
            $header[] = sprintf('Content-Length: %d', strlen($__data));

            $context['http']['header'] = join("\r\n", $header);
            $context['http']['content'] = $__data;
        } else {
            $url .= "?" . http_build_query($data);
        }

        if (strpos($endpoint, "https://") === 0 && !$this->isSupportHttps()) {
            throw new \Exception("does not support https wrapper. please enable openssl extension");
        }


        set_error_handler(array($this, "errorHandler"));
        $data = file_get_contents(
            $endpoint . $url,
            false,
            stream_context_create($context)
        );
        restore_error_handler();

        if (is_null($data)) {
            throw new \Exception("JIRA Rest server returns unexpected result.");
        }

        return $data;
    }

    /**
     * @param $errno
     * @param $errstr
     * @throws \Exception
     */
    public function errorHandler($errno, $errstr)
    {
        throw new \Exception($errstr);
    }
}