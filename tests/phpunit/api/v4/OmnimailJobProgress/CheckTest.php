<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use Civi\Api4\OmnimailJobProgress;

/**
 * OmnimailJobProgress API Test Case
 * @group headless
 */
class api_v4_CheckTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * @throws \API_Exception
   */
  public function tearDown() {
    OmnimailJobProgress::delete(FALSE)->addWhere('job', '=', 'omnimail_privacy_erase')->execute();
    parent::tearDown();
  }

  /**
   * The setup() method is executed before the test is executed (optional).
   */
  public function setUp() {
    $table = CRM_Core_DAO_AllCoreTables::getTableForEntityName('OmnimailJobProgress');
    $this->assertTrue($table && CRM_Core_DAO::checkTableExists($table), 'There was a problem with extension installation. Table for ' . 'OmnimailJobProgress' . ' not found.');
    parent::setUp();
  }

  /**
   * Test the Check function throws an exception when the entry is one hour +
   * old.
   *
   * @throws \API_Exception
   *
   */
  public function testCheck() {
    OmnimailJobProgress::create(FALSE)->setValues(['job' => 'omnimail_privacy_erase', 'mailing_provider' => 'Silvepop'])->execute();
    OmnimailJobProgress::check(FALSE)->execute();
    // Now update to be old. Much like having kids.
    OmnimailJobProgress::update(FALSE)
      ->setValues(['created_date' => '2 hours ago'])
      ->addWhere('job', '=', 'omnimail_privacy_erase')
      ->execute();

    $this->expectException('API_Exception');
    OmnimailJobProgress::check(FALSE)->execute();
  }

}
