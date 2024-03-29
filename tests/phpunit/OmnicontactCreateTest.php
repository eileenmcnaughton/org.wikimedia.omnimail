<?php

require_once __DIR__ . '/OmnimailBaseTestClass.php';

use Civi\Api4\Contact;
use Civi\Api4\Email;
use Civi\Api4\Group;
use Civi\Api4\Omnicontact;
use Civi\Api4\Queue;

/**
 * Test Omnicontact create method.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class OmnicontactCreateTest extends OmnimailBaseTestClass {

  /**
   * Post test cleanup.
   *
   * @throws \API_Exception
   * @throws \CRM_Core_Exception
   */
  public function tearDown(): void {
    Group::delete(FALSE)->addWhere('name', '=', 'test_create_group')->execute();
    parent::tearDown();
  }

  /**
   * Example: the groupMember load fn works.
   *
   * @throws \API_Exception
   */
  public function testAddToGroup(): void {
    $this->getMockRequest([file_get_contents(__DIR__ . '/Responses/AddRecipient.txt')]);
    $group = Group::create(FALSE)->setValues([
      'name' => 'test_create_group',
      'title' => 'Test group create',
      'Group_Metadata.remote_group_identifier' => 42192504,
    ])->execute()->first();
    $result = Omnicontact::create(FALSE)
      ->setGroupID([$group['id']])
      ->setDatabaseID(1234)
      ->setClient($this->getGuzzleClient())
      ->setEmail('jenny@example.com')
      ->setValues([
        'last_name' => 'Jenny',
        'first_name' => 'Lee',
      ])
      ->execute()->first();
    $this->assertEquals(569624660942, $result['contact_identifier']);
    $this->assertEquals(trim(file_get_contents(__DIR__ . '/Requests/AddRecipient.txt')), $this->getRequestBodies()[0]);

  }

  /**
   * Example: the groupMember load fn works.
   *
   * @throws \API_Exception
   */
  public function testSnooze(): void {
    $this->getMockRequest([
      file_get_contents(__DIR__ . '/Responses/AddRecipient.txt'),
      file_get_contents(__DIR__ . '/Responses/UpdateRecipient.txt'),
    ]);

    Omnicontact::create(FALSE)
      ->setEmail('the_don@example.org')
      ->setClient($this->getGuzzleClient())
      ->setDatabaseID(1234)
      ->setValues([
        'last_name' => 'Donald',
        'first_name' => 'Duck',
        'snooze_end_date' => '2023-09-09',
      ])
      ->execute()->first();
    $this->assertEquals(trim(file_get_contents(__DIR__ . '/Requests/SnoozeRecipient.txt')), $this->getRequestBodies()[1]);
  }

  /**
   * Test that updating the contact's snooze date queues up a wee nap.
   *
   * This tests that when a contact has their email updated, using apiv4
   * a request to update Acoustic is saved to the civicrm_queue/civicrm_queue_item
   * table and that when we run that queue it does the call to Acoustic.
   *
   * @throws \CRM_Core_Exception
   */
  public function testQueueSnooze(): void {
    $this->getMockRequest([
      file_get_contents(__DIR__ . '/Responses/AddRecipient.txt'),
      file_get_contents(__DIR__ . '/Responses/UpdateRecipient.txt'),
    ]);
    // These values are passed into the api call in other tests. But, because in this
    // case the hook queues up the database update we need a more 'global' approach.
    $this->setDatabaseID(1234);
    $this->addTestClientToXMLSingleton();

    $snoozeDate =  date('Y-m-d', strtotime('+ 1 week'));
    Contact::create(FALSE)->setValues([
      'contact_type' => 'Individual',
      'first_name' => 'Donald',
      'last_name' => 'Duck',
      'primary_email.email' => 'the_don@example.com',
      'primary_email.email_settings.snooze_date' => $snoozeDate,
    ])->execute();
    $queue = Queue::get(FALSE)
      ->addWhere('name', '=', 'omni-snooze')
      ->addWhere('status', '=', 'active')
      ->execute();
    $this->assertCount(1, $queue);
    $this->assertEquals('active', $queue->first()['status']);
    $this->runQueue();
    $requestContent = str_replace(urlencode('RESUME_SEND_DATE>09/09/2023'), 'RESUME_SEND_DATE' . urlencode('>' . date('m/d/Y', strtotime($snoozeDate))), trim(file_get_contents(__DIR__ . '/Requests/SnoozeRecipient.txt')));
    $this->assertEquals($requestContent, $this->getRequestBodies()[1]);
  }

  /**
   * This also tests that setting snooze date queues an update, but for email entity edit.
   *
   * In this case we will just check it is queued.
   *
   * @throws \CRM_Core_Exception
   */
  public function testQueueEmailEdit(): void {
    // We don't send calls in this test but get an enotice on CI if there is
    // no Acoustic configured.
    $this->setDatabaseID(1234);
    $contactID = Contact::create(FALSE)->setValues([
      'contact_type' => 'Individual',
      'first_name' => 'Daisy',
      'last_name' => 'Duck',
    ])->execute()->first()['id'];

    $snoozeDate =  date('Y-m-d', strtotime('+ 1 week'));
    Email::create(FALSE)->setValues([
      'contact_id' => $contactID,
      'email' => 'daisy@example.com',
      'email_settings.snooze_date' => $snoozeDate,
    ])->execute();
    $this->assertEquals(1, CRM_Core_DAO::singleValueQuery('SELECT COUNT(*) FROM civicrm_queue WHERE name = "omni-snooze"'));
    $queuedItem = Queue::claimItems(FALSE)
      ->setQueue('omni-snooze')
      ->execute()->first();
    $this->assertNotEmpty($queuedItem);
    $this->assertEquals('daisy@example.com', $queuedItem['data']['arguments'][2]['email']);
  }

  /**
   * Run the snooze queue.
   */
  protected function runQueue(): void {
    $queue = Civi::queue('omni-snooze');
    $runner = new CRM_Queue_Runner([
      'queue' => $queue,
      'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
    ]);
    $runner->runAll();
  }

}
