<?php

namespace Tests\chobie\Jira;


use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class ApiTest
 *
 * @package Tests\chobie\Jira
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{

	const ENDPOINT = 'http://jira.company.com';

	/**
	 * Api.
	 *
	 * @var Api
	 */
	protected $api;

	/**
	 * Credential.
	 *
	 * @var AuthenticationInterface
	 */
	protected $credential;

	/**
	 * Client.
	 *
	 * @var ObjectProphecy
	 */
	protected $client;

	protected function setUp()
	{
		parent::setUp();

		$this->credential = $this->prophesize('chobie\Jira\Api\Authentication\AuthenticationInterface')->reveal();
		$this->client = $this->prophesize('chobie\Jira\Api\Client\ClientInterface');

		$this->api = new Api(self::ENDPOINT, $this->credential, $this->client->reveal());
	}

	/**
	 * @dataProvider setEndpointDataProvider
	 */
	public function testSetEndpoint($given_endpoint, $used_endpoint)
	{
		$api = new Api($given_endpoint, $this->credential, $this->client->reveal());
		$this->assertEquals($used_endpoint, $api->getEndpoint());
	}

	public function setEndpointDataProvider()
	{
		return array(
			'trailing slash removed' => array('https://test.test/', 'https://test.test'),
			'nothing removed' => array('https://test.test', 'https://test.test'),
		);
	}

	public function testUpdateVersion()
	{
		$params = array(
			'overdue' => true,
			'description' => 'new description',
		);

		$this->expectClientCall(
			Api::REQUEST_PUT,
			'/rest/api/2/version/111000',
			$params,
			''
		);

		$this->assertFalse($this->api->updateVersion(111000, $params));
	}

	public function testReleaseVersionAutomaticReleaseDate()
	{
		$params = array(
			'released' => true,
			'releaseDate' => date('Y-m-d'),
		);

		$this->expectClientCall(
			Api::REQUEST_PUT,
			'/rest/api/2/version/111000',
			$params,
			''
		);

		$this->assertFalse($this->api->releaseVersion(111000));
	}

	public function testReleaseVersionParameterMerging()
	{
		$release_date = '2010-07-06';

		$expected_params = array(
			'released' => true,
			'releaseDate' => $release_date,
			'test' => 'extra',
		);

		$this->expectClientCall(
			Api::REQUEST_PUT,
			'/rest/api/2/version/111000',
			$expected_params,
			''
		);

		$this->assertFalse($this->api->releaseVersion(111000, $release_date, array('test' => 'extra')));
	}

	public function testFindVersionByName()
	{
		$project_key = 'POR';
		$version_id = '14206';
		$version_name = '3.36.0';

		$versions = array(
			array('id' => '14205', 'name' => '3.62.0'),
			array('id' => $version_id, 'name' => $version_name),
			array('id' => '14207', 'name' => '3.66.0'),
		);

		$this->expectClientCall(
			Api::REQUEST_GET,
			'/rest/api/2/project/' . $project_key . '/versions',
			array(),
			json_encode($versions)
		);

		$this->assertEquals(
			array('id' => $version_id, 'name' => $version_name),
			$this->api->findVersionByName($project_key, $version_name),
			'Version found'
		);

		$this->assertNull(
			$this->api->findVersionByName($project_key, 'i_do_not_exist')
		);
	}

	/**
	 * Expects a particular client call.
	 *
	 * @param string       $method       Request method.
	 * @param string       $url          URL.
	 * @param array|string $data         Request data.
	 * @param string       $return_value Return value.
	 * @param boolean      $is_file      This is a file upload request.
	 * @param boolean      $debug        Debug this request.
	 *
	 * @return void
	 */
	protected function expectClientCall(
		$method,
		$url,
		$data = array(),
		$return_value,
		$is_file = false,
		$debug = false
	) {
		$this->client
			->sendRequest($method, $url, $data, self::ENDPOINT, $this->credential, $is_file, $debug)
			->willReturn($return_value)
			->shouldBeCalled();
	}

}
