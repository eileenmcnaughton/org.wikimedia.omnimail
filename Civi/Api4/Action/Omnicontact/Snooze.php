<?php

namespace Civi\Api4\Action\Omnicontact;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

/**
 * @method string|null getEmail()
 * @method $this setEmail(?string $email)
 * @method string|null getSnoozeDate()
 * @method $this setSnoozeDate(?string $contactID)
 * @method $this setDatabaseID(int $databaseID)
 * @method $this setMailProvider(string $mailProvider) Generally Silverpop....
 * @method string getMailProvider()
 */
class Snooze extends AbstractAction{

  protected $email;
  protected $databaseID;
  protected $snoozeDate;

  /**
   * @var string
   */
  protected $mailProvider = 'Silverpop';

  /**
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $queueName = 'omni-snooze';
    $queue = \Civi::queue($queueName, [
      'type' => 'Sql',
      'runner' => 'task',
      'retry_limit' => 3,
      'retry_interval' => 20,
      'error' => 'abort',
    ]);
    if (!$this->getEmail()) {
      throw new \CRM_Core_Exception('Email required.');
    }
    $queue->createItem(new \CRM_Queue_Task('civicrm_api4_queue',
      [
        'Omnicontact',
        'create',
        [
          'databaseID' => $this->getDatabaseID(),
          'email' => $this->getEmail(),
          'checkPermissions' => $this->getCheckPermissions(),
          'values' => [
            'snooze_end_date' => date('Y-m-d H:i:s', strtotime($this->getSnoozeDate())),
          ],
        ]
      ],
      'Snooze contact'
    ));

    $result[] = [
      'queue_name' => $queueName,
      'email' => $this->getEmail(),
      'snoozeDate' => date('Y-m-d H:i:s', strtotime($this->getSnoozeDate())),
    ];
  }

  /**
   * Get the remote database ID.
   *
   * @return int
   */
  public function getDatabaseID(): int {
    if (!$this->databaseID) {
      $this->databaseID = \Civi::settings()->get('omnimail_credentials')[$this->getMailProvider()]['database_id'][0];
    }
    return $this->databaseID;
  }

}
