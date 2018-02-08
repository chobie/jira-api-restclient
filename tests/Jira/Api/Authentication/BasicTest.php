<?php

namespace Tests\chobie\Jira;


use chobie\Jira\Api\Authentication\Basic;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{

	public function testBasicAuthentication()
	{
		$id = 'abc';
		$pass = 'def';

		$basic = new Basic($id, $pass);
		$this->assertEquals($id, $basic->getId());
		$this->assertEquals($pass, $basic->getPassword());
		$this->assertEquals(base64_encode(sprintf('%s:%s', $id, $pass)), $basic->getCredential());
	}

}

