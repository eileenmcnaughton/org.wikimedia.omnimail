<?php

use Omnimail\Silverpop\Responses\RecipientsResponse;
use Omnimail\Omnimail;

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/16/17
 * Time: 5:53 PM
 */

class CRM_Omnimail_Omnimail {

  /**
   * @return CRM_Omnimail_Omnimail
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new self();
    }
    return self::$singleton;
  }

  /**
   * @param array $params
   */
  public function getMailer($params) {

  }

  /**
   * Get the timestamp to start from.
   *
   * @param array $params
   * @param array $jobSettings
   *
   * @return string
   */
  protected static function getStartTImestamp($params, $jobSettings) {
    if (isset($params['start_date'])) {
      return strtotime($params['start_date']);
    }
    if (!empty($jobSettings['last_timestamp'])) {
      return $jobSettings['last_timestamp'];
    }
    return strtotime('450 days ago');
  }

  /**
   * Get the end timestamp, bearing in mind our poor ability to read the future.
   *
   * @param string $passedInEndDate
   * @param array $settings
   * @param string $startTimestamp
   *
   * @return false|int
   */
  protected static function getEndTimestamp($passedInEndDate, $settings, $startTimestamp) {
    if ($passedInEndDate) {
      $endTimeStamp = strtotime($passedInEndDate);
    }
    else {
      $adjustment = CRM_Utils_Array::value('omnimail_job_default_time_interval', $settings, ' + 1 day');
      $endTimeStamp = strtotime($adjustment, $startTimestamp);
    }
    return ($endTimeStamp > strtotime('now') ? strtotime('now') : $endTimeStamp);
  }

  /**
   * @param $params
   * @return array
   */
  public function getJobSettings($params) {
    $settings = CRM_Omnimail_Helper::getSettings();
    $jobSettings = CRM_Utils_Array::value($params['mail_provider'], $settings['omnimail_' . $this->job . '_load'], array());
    return $jobSettings;
  }

}