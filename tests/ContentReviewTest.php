<?php

class ContentReviewTest extends FunctionalTest {
	static $fixture_file = 'contentreview/tests/ContentReviewTest.yml';
	
	function testPermissions() {
		$editor = $this->objFromFixture('Member', 'editor');
		$author = $this->objFromFixture('Member', 'author');
		
		// Assert the permission code exists
		$perms = singleton('SiteTreeContentReview')->providePermissions();
		$this->assertTrue(isset($perms['EDIT_CONTENT_REVIEW_FIELDS']));
		
		// Check a user with permission can edit fields
		$this->logInAs($editor);
		$page = new Page();
		$fields = $page->getCMSFields();
		$this->assertNotNull($fields->fieldByName('Root.Review'));
		
		// Check a user without permission can see tab
		$this->logInAs($author);
		$page = new Page();
		$fields = $page->getCMSFields();
		$this->assertNull($fields->fieldByName('Root.Review'));
	}
	
	function testContentReviewEmails() {
		SS_Datetime::set_mock_now('2010-02-14 12:00:00');
		
		$task = new ContentReviewEmails();
		$task->run(new SS_HTTPRequest('GET', '/dev/tasks/ContentReviewEmails'));
		
		$this->assertEmailSent('author@example.com', null, sprintf(_t('ContentReviewEmails.SUBJECT', 'Page %s due for content review'), 'Staff'));
		
		SS_Datetime::clear_mock_now();
	}
	
	function testAutomaticallySettingReviewDate() {
		$editor = $this->objFromFixture('Member', 'editor');
		$this->logInAs($editor);
		
		$page = new Page();
		$page->ReviewPeriodDays = 10;
		$page->write();
		$this->assertTrue($page->doPublish());		
		$this->assertEquals(date('Y-m-d', strtotime('now + 10 days')), $page->NextReviewDate);
	}
	
	function testReportContent() {
		$editor = $this->objFromFixture('Member', 'editor');
		$this->logInAs($editor);
		$report = new PagesDueForReviewReport();
		
		$report->parameterFields();
		$report->columns();
		$report->title();
		
		$results = $report->sourceRecords(array(
			'ReviewDateAfter' => '01/01/2010',
			'ReviewDateBefore' => '12/12/2010'
		), 'NextReviewDate ASC', false);
		
		$this->assertEquals($results->column('Title'), array(
			'Home',
			'About Us',
			'Staff',
			'Contact Us'
		));
		
		SS_Datetime::set_mock_now('2010-02-13 12:00:00');
		
		$results = $report->sourceRecords(array(
		), 'NextReviewDate ASC', false);
		
		$this->assertEquals($results->column('Title'), array(
			'Home',
			'About Us'
		));
		
		SS_Datetime::clear_mock_now();
	}

	function testOwners() {
		$editor = $this->objFromFixture('Member', 'editor');
		$this->logInAs($editor);
		
		$page = new Page();		
		$page->ReviewPeriodDays = 10;
		$page->Title = 'Test_write';
		$page->Owners()->removeAll();
		$page->Owners()->add($editor);
		$page->Owners()->write();
		$id = $page->write();

		$this->assertTrue($page->doPublish());
		// For some reason this iteration approach works, but simply calling
		// $page->Owners()->First() does not.
		foreach($page->Owners() as $o) {
			$this->assertNotNull($o);
			$this->assertEquals($o->getName(), "Test Editor");
			break;
		}
		
		$page = $this->objFromFixture('Page', 'about');
		$page->Owners()->removeAll();
		$page->write();
		
		$this->assertTrue($page->doPublish());
		$this->assertNull($page->Owners()->First());
	}
}
