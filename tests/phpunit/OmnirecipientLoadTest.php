<?php

use Civi\Test\EndToEndInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
require_once __DIR__ . '/OmnimailBaseTestClass.php';

/**
 * FIXME - Add test description.
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
 * @group e2e
 */
class OmnirecipientLoadTest extends OmnimailBaseTestClass implements EndToEndInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::e2e()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Example: Test that a version is returned.
   */
  public function testOmnirecipientLoad() {
    $responses = array(
      file_get_contents(__DIR__ . '/Responses/RawRecipientDataExportResponse.txt'),
      file_get_contents(__DIR__ . '/Responses/jobStatusCompleteResponse.txt'),
    );
    //Raw Recipient Data Export Jul 02 2017 21-46-49 PM 758.zip
    copy(__DIR__ . '/Responses/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv', sys_get_temp_dir() . '/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv');
    fopen(sys_get_temp_dir() . '/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv.complete', 'c');

    civicrm_api3('Omnirecipient', 'load', array('mail_provider' => 'Silverpop', 'username' => 'Donald', 'password' => 'Duck', 'debug' => 1, 'client' => $this->getMockRequest($responses)));
    $providers = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_mailing_provider_data')->fetchAll();
    $this->assertEquals(array(
      0 => array(
        'contact_identifier' => '126312673126',
        'mailing_identifier' => '54132674',
        'email' => 'sarah@example.com',
        'event_type' => 'Open',
        'recipient_action_datetime' => '2017-06-30 23:32:00',
        'contact_id' => '',
        'is_civicrm_updated' => '0',
      ),
      1 => array(
        'contact_identifier' => '15915939159',
        'mailing_identifier' => '54132674',
        'email' => 'cliff@example.com',
        'event_type' => 'Open',
        'recipient_action_datetime' => '2017-06-30 23:32:00',
        'contact_id' => '',
        'is_civicrm_updated' => '0',
      ),
      2 => array(
        'contact_identifier' => '248248624848',
        'mailing_identifier' => '54132674',
        'email' => 'bob@example.com',
        'event_type' => 'Open',
        'recipient_action_datetime' => '2017-06-30 23:32:00',
        'contact_id' => '123',
        'is_civicrm_updated' => '0',
      ),
      3 => array(
        'contact_identifier' => '508505678505',
        'mailing_identifier' => '54132674',
        'email' => 'steve@example.com',
        'event_type' => 'Open',
        'recipient_action_datetime' => '2017-07-01 17:28:00',
        'contact_id' => '456',
        'is_civicrm_updated' => '0',
      ),
    ), $providers);

  }

  /**
   * Test when download does not complete in time.
   */
  public function testOmnirecipientLoadIncomplete() {
    $responses = array(
      file_get_contents(__DIR__ . '/Responses/RawRecipientDataExportResponse.txt'),
    );
    for ($i = 0; $i < 15; $i++) {
      $responses[] = file_get_contents(__DIR__ . '/Responses/jobStatusWaitingResponse.txt');
    }
    civicrm_api3('setting', 'create', array('omnimail_job_retry_interval' => 0.01));
    civicrm_api3('Omnirecipient', 'load', array('mail_provider' => 'Silverpop', 'username' => 'Donald', 'password' => 'Duck', 'debug' => 1, 'client' => $this->getMockRequest($responses)));
    $this->assertEquals(0, CRM_Core_DAO::singleValueQuery('SELECT  count(*) FROM civicrm_mailing_provider_data'));

    $jobSettings = CRM_Omnimail_Omnirecipients::getJobSettings(array('mail_provider' => 'Silverpop'));
    $this->assertEquals(array(
      'jobId' => '101569750',
      'filePath' => 'Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.zip',
    ), $jobSettings['retrieval_parameters']);
  }

}
