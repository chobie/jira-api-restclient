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

namespace Chobie\JiraApiRestClient\Jira\Api\Client;

use Chobie\JiraApiRestClient\Jira\Api\Authentication\Anonymous;
use Chobie\JiraApiRestClient\Jira\Api\Authentication\AuthenticationInterface;
use Chobie\JiraApiRestClient\Jira\Api\Authentication\Basic;

class PHPClient implements ClientInterface
{
    /**
     * HTTPS support enabled.
     *
     * @var bool
     */
    protected $httpsSupport = false;

    /**
     * Create a traditional php client.
     */
    public function __construct()
    {
        $wrappers = stream_get_wrappers();

        if (in_array('https', $wrappers)) {
            $this->httpsSupport = true;
        }
    }

    /**
     * Returns status of HTTP support.
     *
     * @return bool
     */
    protected function isHttpsSupported()
    {
        return $this->httpsSupport;
    }

    /**
     * Sends request to the API server.
     *
     * @param string                  $method     Request method.
     * @param string                  $url        URL.
     * @param array|string            $data       Request data.
     * @param string                  $endpoint   Endpoint.
     * @param AuthenticationInterface $credential Credential.
     * @param bool                    $is_file    This is a file upload request.
     * @param bool                    $debug      Debug this request.
     *
     * @return array|string
     *
     * @throws \Exception When non-supported implementation of AuthenticationInterface is given.
     */
    public function sendRequest(
        $method,
        $url,
        $data = [],
        $endpoint,
        AuthenticationInterface $credential,
        $is_file = false,
        $debug = false
    ) {
        if (!($credential instanceof Basic) && !($credential instanceof Anonymous)) {
            throw new \Exception(sprintf('PHPClient does not support %s authentication.', get_class($credential)));
        }

        $header = [];

        if (!($credential instanceof Anonymous)) {
            $header[] = 'Authorization: Basic '.$credential->getCredential();
        }

        $context = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $header),
            ],
        ];

        if (!$is_file) {
            $header[] = 'Content-Type: application/json;charset=UTF-8';
        }

        if ($method == 'POST' || $method == 'PUT') {
            if ($is_file) {
                $filename = preg_replace('/^@/', '', $data['file']);
                $boundary = '--------------------------'.microtime(true);
                $header[] = 'X-Atlassian-Token: nocheck';
                $header[] = 'Content-Type: multipart/form-data; boundary='.$boundary;

                $__data = '--'.$boundary."\r\n".
                    'Content-Disposition: form-data; name="file"; filename="'.basename($filename)."\"\r\n".
                    "Content-Type: application/octet-stream\r\n\r\n".
                    file_get_contents($filename)."\r\n";
                $__data .= '--'.$boundary."--\r\n";
            } else {
                $__data = json_encode($data);
            }

            $header[] = sprintf('Content-Length: %d', strlen($__data));

            $context['http']['header'] = implode("\r\n", $header);
            $context['http']['content'] = $__data;
        } else {
            $url .= '?'.http_build_query($data);
        }

        if (strpos($endpoint, 'https://') === 0 && !$this->isHttpsSupported()) {
            throw new \Exception('does not support https wrapper. please enable openssl extension');
        }

        set_error_handler([$this, 'errorHandler']);
        $data = file_get_contents(
            $endpoint.$url,
            false,
            stream_context_create($context)
        );
        restore_error_handler();

        if (is_null($data)) {
            throw new \Exception('JIRA Rest server returns unexpected result.');
        }

        return $data;
    }

    /**
     * Throws exception on error.
     *
     * @param int    $errno  Error number.
     * @param string $errstr Error message.
     *
     * @throws \Exception Always.
     */
    public function errorHandler($errno, $errstr)
    {
        throw new \Exception($errstr, $errno);
    }
}
