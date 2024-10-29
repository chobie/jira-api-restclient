<?php

namespace Tests\chobie\Jira;


use chobie\Jira\IssueType;
use PHPUnit\Framework\TestCase;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class IssueTypeTest extends TestCase
{
	use ExpectException;

	public function testHandlesSingleIssueTypeWithAvatarId()
	{
		$issue_type_source = array(
			'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
			'id' => '4',
			'description' => 'An improvement or enhancement to an existing feature or task.',
			'entityId' => '9d7dd6f7-e8b6-4247-954b-7b2c9b2a5ba2',
			'iconUrl' => 'https://hosted.atlassian.net/secure/viewavatar?size=xsmall&avatarId=1&avatarType=issuetype',
			'name' => 'Improvement',
			'scope' => array(
				'project' => array('id' => '10000'),
				'type' => 'PROJECT',
			),
			'untranslatedName' => 'Epic',
			'subtask' => false,
			'hierarchyLevel' => 1,
			'avatarId' => 1,
		);
		$issue_type = new IssueType($issue_type_source);
		$this->assertEquals($issue_type->getSelf(), $issue_type_source['self']);
		$this->assertEquals($issue_type->getId(), $issue_type_source['id']);
		$this->assertEquals($issue_type->getDescription(), $issue_type_source['description']);
		$this->assertEquals($issue_type->getEntityId(), $issue_type_source['entityId']);
		$this->assertEquals($issue_type->getIconUrl(), $issue_type_source['iconUrl']);
		$this->assertEquals($issue_type->getName(), $issue_type_source['name']);
		$this->assertEquals($issue_type->getScope(), $issue_type_source['scope']);
		$this->assertEquals($issue_type->isSubtask(), $issue_type_source['subtask']);
		$this->assertEquals($issue_type->getUntranslatedName(), $issue_type_source['untranslatedName']);
		$this->assertEquals($issue_type->getHierarchyLevel(), $issue_type_source['hierarchyLevel']);
		$this->assertEquals($issue_type->getAvatarId(), $issue_type_source['avatarId']);
	}

	public function testHandlesSingleIssueTypeWithoutAvatarId()
	{
		$issue_type_source = array(
			'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
			'id' => '4',
			'description' => 'An improvement or enhancement to an existing feature or task.',
			'iconUrl' => 'https://hosted.atlassian.net/secure/viewavatar?size=xsmall&avatarId=1&avatarType=issuetype',
			'name' => 'Improvement',
			'subtask' => false,
		);
		$issue_type = new IssueType($issue_type_source);
		$this->assertEquals($issue_type->getId(), $issue_type_source['id']);
		$this->assertEquals($issue_type->getDescription(), $issue_type_source['description']);
		$this->assertEquals($issue_type->getIconUrl(), $issue_type_source['iconUrl']);
		$this->assertEquals($issue_type->getName(), $issue_type_source['name']);
	}

	public function testGettingUnknownProperty()
	{
		$issue_type_source = array(
			'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
		);
		$issue_type = new IssueType($issue_type_source);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('The "chobie\Jira\IssueType::getUnknown" method does not exist.');
		$issue_type->getUnknown();
	}

	public function testCreatingWithUnknownField()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('The "unknown" issue type keys are not supported.');

		$issue_type_source = array(
			'self' => 'https://hosted.atlassian.net/rest/api/2/issuetype/4',
			'unknown' => '4',
		);
		new IssueType($issue_type_source);
	}

}
