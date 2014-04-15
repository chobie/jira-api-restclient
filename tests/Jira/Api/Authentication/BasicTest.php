<?php

class  Jira_Api_Authentication_BasicTest extends PHPUnit_Framework_TestCase
{
    public function testBasicAuthentication()
    {
        $id = "abc";
        $pass = "def";

        $basic = new Jira_Api_Authentication_Basic($id, $pass);
        $this->assertEquals($id, $basic->getId());
        $this->assertEquals($pass, $basic->getPassword());
        $this->assertEquals(base64_encode(sprintf("%s:%s", $id, $pass)), $basic->getCredential());
    }
}

