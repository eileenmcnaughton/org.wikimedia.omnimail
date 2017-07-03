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
class OmnirecipientGetTest extends OmnimailBaseTestClass implements EndToEndInterface, TransactionalInterface {

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
  public function testOmnirecipientGet() {
    $responses = array(
      file_get_contents(__DIR__ . '/Responses/RawRecipientDataExportResponse.txt'),
      file_get_contents(__DIR__ . '/Responses/jobStatusCompleteResponse.txt'),
    );
    //Raw Recipient Data Export Jul 02 2017 21-46-49 PM 758.zip
    copy(__DIR__ . '/Responses/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv', sys_get_temp_dir() . '/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv');
    fopen(sys_get_temp_dir() . '/Raw Recipient Data Export Jul 03 2017 00-47-42 AM 1295.csv.complete', 'c');

    $result = civicrm_api3('Omnirecipient', 'get', array('mail_provider' => 'Silverpop', 'username' => 'Shrek', 'password' => 'Fiona', 'options' => array('limit' => 3), 'client' => $this->getMockRequest($responses)));
    $this->assertEquals(3, $result['count']);
    $this->assertEquals('bob@example.com', $result['values'][0]['email']);
    $this->assertEquals('123', $result['values'][0]['contact_id']);
  }

}
