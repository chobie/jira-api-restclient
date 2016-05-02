<?php

namespace Tests\chobie\Jira;

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Anonymous;

/**
 * Class ApiTest
 *
 * @package Tests\chobie\Jira
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that any trailing slash in the endpoint url is removed before being stored in the object state
     */
    public function testSetEndpointTrailingSlash()
    {
        $api = new Api('https://test.test/', new Anonymous(), null);
        $this->assertEquals('https://test.test', $api->getEndpoint());

        // Make sure nothing is removed if there is no trailing slash
        $url = 'https://urlwithouttrailing.slash';
        $api->setEndPoint($url);
        $this->assertEquals($url, $api->getEndpoint());
    }

    /**
     * Tests that the updateVersion call constructs the correct api call
     */
    public function testUpdateVersion()
    {
        $params = array(
            'released' => true,
            'releaseDate' => '2010-07-06',
        );

        // Stub the api method and keep the rest intact
        /** @var Api|\PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('\chobie\Jira\Api')->setMethods(array('api'))->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('api')->with(
            $this->equalTo(Api::REQUEST_PUT),
            $this->equalTo('/rest/api/2/version/111000'),
            $this->equalTo($params)
        );

        $api->updateVersion(111000, $params);
    }

    /**
     * Tests that the releaseVersion call constructs the correct api call
     */
    public function testReleaseVersion()
    {
        $params = array(
            'released' => true,
            'releaseDate' => date('Y-m-d'),
        );

        // Stub the api method and keep the rest intact
        /** @var Api|\PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('\chobie\Jira\Api')->setMethods(array('api'))->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('api')->with(
            $this->equalTo(Api::REQUEST_PUT),
            $this->equalTo('/rest/api/2/version/111000'),
            $this->equalTo($params)
        );

        $api->releaseVersion(111000);
    }

    /**
     * Tests that the releaseVersion call constructs the correct api call with overriden release data and params
     */
    public function testReleaseVersionAdvanced()
    {
        $releaseDate = '2010-07-06';

        $params = array(
            'released' => true,
            'releaseDate' => $releaseDate,
            'test' => 'extra'
        );

        // Stub the api method and keep the rest intact
        /** @var Api|\PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('\chobie\Jira\Api')->setMethods(array('api'))->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('api')->with(
            $this->equalTo(Api::REQUEST_PUT),
            $this->equalTo('/rest/api/2/version/111000'),
            $this->equalTo($params)
        );

        $api->releaseVersion(111000, $releaseDate, array('test' => 'extra'));
    }

    /**
     * Tests FindVersionByName
     */
    public function testFindVersionByName()
    {
        $name = '3.36.0';
        $versionId = '14206';
        $projectKey = 'POR';

        $versions = array(
            array('id' => '14205', 'name' => '3.62.0'),
            array('id' => $versionId, 'name' => $name),
            array('id' => '14207', 'name' => '3.66.0'),
        );

        // Stub the getVersions method and keep the rest intact
        /** @var Api|\PHPUnit_Framework_MockObject_MockObject $api */
        $api = $this->getMockBuilder('\chobie\Jira\Api')->setMethods(array('getVersions'))->disableOriginalConstructor()->getMock();
        $api->expects($this->exactly(2))->method('getVersions')->with(
            $this->equalTo($projectKey)
        )->willReturn($versions);

        // He should find this one
        $this->assertEquals(array('id' => $versionId, 'name' => $name), $api->findVersionByName($projectKey, $name));

        // And there should be no result for this one
        $this->assertNull($api->findVersionByName($projectKey, 'i_do_not_exist'));
    }
}
