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


use chobie\Jira\Api\Authentication\AuthenticationInterface;

class MemcacheProxyClient implements ClientInterface
{

	/**
	 * Client.
	 *
	 * @var ClientInterface
	 */
	protected $client;

	/**
	 * Memcache.
	 *
	 * @var \Memcached
	 */
	protected $mc;

	/**
	 * Create wrapper around other client.
	 *
	 * @param ClientInterface $client Client.
	 * @param string          $server Server.
	 * @param integer         $port   Port.
	 */
	public function __construct(ClientInterface $client, $server, $port)
	{
		$this->client = $client;

		$this->mc = new \Memcached();
		$this->mc->addServer($server, $port);
	}

	/**
	 * Sends request to the API server.
	 *
	 * @param string                  $method     Request method.
	 * @param string                  $url        URL.
	 * @param array                   $data       Request data.
	 * @param string                  $endpoint   Endpoint.
	 * @param AuthenticationInterface $credential Credential.
	 * @param boolean                 $is_file    This is a file upload request.
	 * @param boolean                 $debug      Debug this request.
	 *
	 * @return array|string
	 */
	public function sendRequest(
		$method,
		$url,
		$data = array(),
		$endpoint,
		AuthenticationInterface $credential,
		$is_file = false,
		$debug = false
	) {
		if ( $method == 'GET' ) {
			$result = $this->getFromCache($url, $data, $endpoint);

			if ( $result ) {
				// $this->setCache($url, $data, $endpoint, $result);
				return $result;
			}
		}

		$result = $this->client->sendRequest($method, $url, $data, $endpoint, $credential);

		if ( $method == 'GET' ) {
			$this->setCache($url, $data, $endpoint, $result);
		}

		return $result;
	}

	/**
	 * Retrieves data from cache.
	 *
	 * @param string $url      URL.
	 * @param array  $data     Data.
	 * @param string $endpoint Endpoint.
	 *
	 * @return mixed
	 */
	protected function getFromCache($url, array $data, $endpoint)
	{
		$key = $endpoint . $url;
		$key .= http_build_query($data);
		$key = sha1($key);

		return $this->mc->get('jira:cache:' . $key);
	}

	/**
	 * Sets data into cache.
	 *
	 * @param string $url      URL.
	 * @param array  $data     Data.
	 * @param string $endpoint Endpoint.
	 * @param mixed  $result   Result.
	 *
	 * @return boolean
	 */
	protected function setCache($url, array $data, $endpoint, $result)
	{
		$key = $endpoint . $url;
		$key .= http_build_query($data);
		$key = sha1($key);

		return $this->mc->set('jira:cache:' . $key, $result, 86400);
	}

}
