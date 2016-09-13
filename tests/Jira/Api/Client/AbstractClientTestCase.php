<?php

namespace Tests\chobie\Jira\Api\Client;


use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Anonymous;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Client\ClientInterface;

abstract class AbstractClientTestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * Client.
	 *
	 * @var ClientInterface
	 */
	protected $client;

	protected function setUp()
	{
		parent::setUp();

		if ( empty($_SERVER['REPO_URL']) ) {
			$this->markTestSkipped('The "REPO_URL" environment variable not set.');
		}

		$this->client = $this->createClient();
	}

	/**
	 * @dataProvider getRequestWithKnownHttpCodeDataProvider
	 */
	public function testGetRequestWithKnownHttpCode($http_code)
	{
		$data = array('param1' => 'value1', 'param2' => 'value2');
		$trace_result = $this->traceRequest(Api::REQUEST_GET, array_merge(array('http_code' => $http_code), $data));

		$this->assertEquals('GET', $trace_result['_SERVER']['REQUEST_METHOD']);
		$this->assertContentType('application/json;charset=UTF-8', $trace_result);
		$this->assertEquals($data, $trace_result['_GET']);
	}

	public function getRequestWithKnownHttpCodeDataProvider()
	{
		return array(
			'http 200' => array(200),
			'http 403' => array(403),
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Data must be an array.
	 */
	public function testGetRequestError()
	{
		$this->traceRequest(Api::REQUEST_GET, 'param1=value1&param2=value2');
	}

	public function testPostRequest()
	{
		$data = array('param1' => 'value1', 'param2' => 'value2');
		$trace_result = $this->traceRequest(Api::REQUEST_POST, $data);

		$this->assertEquals('POST', $trace_result['_SERVER']['REQUEST_METHOD']);
		$this->assertContentType('application/json;charset=UTF-8', $trace_result);
		$this->assertEquals(json_encode($data), $trace_result['INPUT']);
	}

	public function testPutRequest()
	{
		$data = array('param1' => 'value1', 'param2' => 'value2');
		$trace_result = $this->traceRequest(Api::REQUEST_PUT, $data);

		$this->assertEquals('PUT', $trace_result['_SERVER']['REQUEST_METHOD']);
		$this->assertContentType('application/json;charset=UTF-8', $trace_result);
		$this->assertEquals(json_encode($data), $trace_result['INPUT']);
	}

	public function testDeleteRequest()
	{
		$data = array('param1' => 'value1', 'param2' => 'value2');
		$trace_result = $this->traceRequest(Api::REQUEST_DELETE, $data);

		$this->assertEquals('DELETE', $trace_result['_SERVER']['REQUEST_METHOD']);
		$this->assertContentType('application/json;charset=UTF-8', $trace_result);
		$this->assertEquals(json_encode($data), $trace_result['INPUT']);
	}

	/**
	 * @dataProvider fileUploadDataProvider
	 */
	public function testFileUpload($filename, $name)
	{
		$upload_file = $filename;
		$data = array('file' => '@' . $upload_file, 'name' => $name);
		$trace_result = $this->traceRequest(Api::REQUEST_POST, $data, null, true);

		$this->assertEquals('POST', $trace_result['_SERVER']['REQUEST_METHOD']);

		$this->assertArrayHasKey('HTTP_X_ATLASSIAN_TOKEN', $trace_result['_SERVER']);
		$this->assertEquals('nocheck', $trace_result['_SERVER']['HTTP_X_ATLASSIAN_TOKEN']);

		$this->assertCount(
			1,
			$trace_result['_FILES'],
			'File was uploaded'
		);
		$this->assertArrayHasKey(
			'file',
			$trace_result['_FILES'],
			'File was uploaded under "file" field name'
		);
		$this->assertEquals(
			($name !== null) ? $name : basename($upload_file),
			$trace_result['_FILES']['file']['name'],
			'Filename is as expected'
		);
		$this->assertNotEmpty($trace_result['_FILES']['file']['type']);
		$this->assertEquals(
			UPLOAD_ERR_OK,
			$trace_result['_FILES']['file']['error'],
			'No upload error happened'
		);
		$this->assertGreaterThan(
			0,
			$trace_result['_FILES']['file']['size'],
			'File is not empty'
		);
	}

	public function fileUploadDataProvider()
	{
		return array(
			'default name' => array('file' => __FILE__, 'name' => null),
			'overridden name' => array('file' => __FILE__, 'name' => 'custom_name.php'),
		);
	}

	public function testUnsupportedCredentialGiven()
	{
		$client_class_parts = explode('\\', get_class($this->client));
		$credential = $this->prophesize('chobie\Jira\Api\Authentication\AuthenticationInterface')->reveal();

		$this->setExpectedException(
			'InvalidArgumentException',
			end($client_class_parts) . ' does not support ' . get_class($credential) . ' authentication.'
		);

		$this->client->sendRequest(Api::REQUEST_GET, 'url', array(), 'endpoint', $credential);
	}

	public function testBasicCredentialGiven()
	{
		$credential = new Basic('user1', 'pass1');

		$trace_result = $this->traceRequest(Api::REQUEST_GET, array(), $credential);

		$this->assertArrayHasKey('PHP_AUTH_USER', $trace_result['_SERVER']);
		$this->assertEquals('user1', $trace_result['_SERVER']['PHP_AUTH_USER']);

		$this->assertArrayHasKey('PHP_AUTH_PW', $trace_result['_SERVER']);
		$this->assertEquals('pass1', $trace_result['_SERVER']['PHP_AUTH_PW']);
	}

	public function testCommunicationError()
	{
		$this->markTestIncomplete('TODO');
	}

	/**
	 * @expectedException \chobie\Jira\Api\UnauthorizedException
	 * @expectedExceptionMessage Unauthorized
	 */
	public function testUnauthorizedRequest()
	{
		$this->traceRequest(Api::REQUEST_GET, array('http_code' => 401));
	}

	/**
	 * @expectedException \chobie\Jira\Api\Exception
	 * @expectedExceptionMessage JIRA Rest server returns unexpected result.
	 */
	public function testEmptyResponseWithUnknownHttpCode()
	{
		$this->traceRequest(Api::REQUEST_GET, array('response_mode' => 'empty'));
	}

	/**
	 * @dataProvider emptyResponseWithKnownHttpCodeDataProvider
	 */
	public function testEmptyResponseWithKnownHttpCode($http_code)
	{
		$this->assertSame(
			'',
			$this->traceRequest(Api::REQUEST_GET, array('http_code' => $http_code, 'response_mode' => 'empty'))
		);
	}

	public function emptyResponseWithKnownHttpCodeDataProvider()
	{
		return array(
			'http 201' => array(201),
			'http 204' => array(204),
		);
	}

	/**
	 * Checks, that request contained specified content type.
	 *
	 * @param string $expected     Expected.
	 * @param array  $trace_result Trace result.
	 *
	 * @return void
	 */
	protected function assertContentType($expected, array $trace_result)
	{
		if ( array_key_exists('CONTENT_TYPE', $trace_result['_SERVER']) ) {
			// Normal Web Server.
			$content_type = $trace_result['_SERVER']['CONTENT_TYPE'];
		}
		elseif ( array_key_exists('HTTP_CONTENT_TYPE', $trace_result['_SERVER']) ) {
			// PHP Built-In Web Server.
			$content_type = $trace_result['_SERVER']['HTTP_CONTENT_TYPE'];
		}
		else {
			$content_type = null;
		}

		$this->assertEquals($expected, $content_type, 'Content type is correct');
	}

	/**
	 * Traces a request.
	 *
	 * @param string                        $method     Request method.
	 * @param array                         $data       Request data.
	 * @param  AuthenticationInterface|null $credential Credential.
	 * @param boolean                       $is_file    This is a file upload request.
	 *
	 * @return array
	 */
	protected function traceRequest(
		$method,
		$data = array(),
		AuthenticationInterface $credential = null,
		$is_file = false
	) {
		if ( !isset($credential) ) {
			$credential = new Anonymous();
		}

		$path_info_variables = array(
			'http_code' => 200,
			'response_mode' => 'trace',
		);

		if ( is_array($data) ) {
			if ( isset($data['http_code']) ) {
				$path_info_variables['http_code'] = $data['http_code'];
				unset($data['http_code']);
			}

			if ( isset($data['response_mode']) ) {
				$path_info_variables['response_mode'] = $data['response_mode'];
				unset($data['response_mode']);
			}
		}

		$path_info = array();

		foreach ( $path_info_variables as $variable_name => $variable_value ) {
			$path_info[] = $variable_name;
			$path_info[] = $variable_value;
		}

		$result = $this->client->sendRequest(
			$method,
			'/tests/debug_response.php/' . implode('/', $path_info) . '/',
			$data,
			rtrim($_SERVER['REPO_URL'], '/'),
			$credential,
			$is_file
		);

		if ( $path_info_variables['response_mode'] === 'trace' ) {
			return unserialize($result);
		}

		return $result;
	}

	/**
	 * Creates client.
	 *
	 * @return ClientInterface
	 */
	abstract protected function createClient();

}
