<?php

namespace Tests\chobie\Jira;


use chobie\Jira\IssueType;

class IssueTypeTest extends \PHPUnit_Framework_TestCase
{

	public function testHandlesSingleIssueTypeWithAvatarId()
	{
		$issueTypeSource = array(
		'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
		'id' => '4',
		'description' => 'An improvement or enhancement to an existing feature or task.',
		'iconUrl' => 'https://hosted.atlassian.net/secure/viewavatar?size=xsmall&avatarId=1&avatarType=issuetype',
		'name' => 'Improvement',
		'subtask' => false,
		'avatarId' => 1,
		);
		$issueType = new IssueType($issueTypeSource);
		$this->assertEquals($issueType->getId(), $issueTypeSource['id']);
		$this->assertEquals($issueType->getDescription(), $issueTypeSource['description']);
		$this->assertEquals($issueType->getIconUrl(), $issueTypeSource['iconUrl']);
		$this->assertEquals($issueType->getName(), $issueTypeSource['name']);
		$this->assertEquals($issueType->getAvatarId(), $issueTypeSource['avatarId']);
	}

	public function testHandlesSingleIssueTypeWithoutAvatarId()
	{
		$issueTypeSource = array(
			'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
			'id' => '4',
			'description' => 'An improvement or enhancement to an existing feature or task.',
			'iconUrl' => 'https://hosted.atlassian.net/secure/viewavatar?size=xsmall&avatarId=1&avatarType=issuetype',
			'name' => 'Improvement',
			'subtask' => false,
		);
		$issueType = new IssueType($issueTypeSource);
		$this->assertEquals($issueType->getId(), $issueTypeSource['id']);
		$this->assertEquals($issueType->getDescription(), $issueTypeSource['description']);
		$this->assertEquals($issueType->getIconUrl(), $issueTypeSource['iconUrl']);
		$this->assertEquals($issueType->getName(), $issueTypeSource['name']);
	}

}
