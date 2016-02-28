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
}
