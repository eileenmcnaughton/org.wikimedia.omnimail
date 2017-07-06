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
class OmnigroupmemberGetTest extends OmnimailBaseTestClass implements EndToEndInterface, TransactionalInterface {

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
  public function testOmnigroupmemberGet() {
    $client = $this->setupSuccessfulDownloadClient();

    $result = civicrm_api3('Omnigroupmember', 'get', array('mail_provider' => 'Silverpop', 'username' => 'Shrek', 'password' => 'Fiona', 'options' => array('limit' => 3), 'client' => $client));
    $this->assertEquals(3, $result['count']);
    $this->assertEquals('eric@example.com', $result['values'][0]['email']);
    $this->assertEquals('', $result['values'][0]['contact_id']);
  }


  /**
   * @return \GuzzleHttp\Client
   */
  protected function setupSuccessfulDownloadClient() {
    $responses = array(
      file_get_contents(__DIR__ . '/Responses/ExportListResponse.txt'),
      file_get_contents(__DIR__ . '/Responses/jobStatusCompleteResponse.txt'),
    );
    copy(__DIR__ . '/Responses/20170509_noCID - All - Jul 5 2017 06-27-45 AM.csv', sys_get_temp_dir() . '/20170509_noCID - All - Jul 5 2017 06-27-45 AM.csv');
    fopen(sys_get_temp_dir() . '/20170509_noCID - All - Jul 5 2017 06-27-45 AM.csv.complete', 'c');
    civicrm_api3('Setting', 'create', array(
      'omnimail_omnigroupmembers_load' => array(
        'Silverpop' => array('last_timestamp' => '1487890800'),
      ),
    ));
    $client = $this->getMockRequest($responses);
    return $client;
  }
}
