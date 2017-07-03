<?php

use Omnimail\Silverpop\Responses\RecipientsResponse;
use Omnimail\Omnimail;
use Omnimail\Silverpop\Credentials;

/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/16/17
 * Time: 5:53 PM
 */

class CRM_Omnimail_Omnirecipients {

  /**
   * @var 
   */
  protected $request;

  /**
   * @param array $params
   *
   * @return \Omnimail\Silverpop\Responses\RecipientsResponse
   * @throws \CRM_Omnimail_IncompleteDownloadException
   */
  static function getResult($params) {
    $jobSettings = self::getJobSettings($params);
    $settings = self::getSettings();

    $mailerCredentials = array('credentials' => new Credentials(array('username' => $params['username'], 'password' => $params['password'])));
    if (!empty($params['client'])) {
      $mailerCredentials['client'] = $params['client'];
    }

    $request = Omnimail::create($params['mail_provider'], $mailerCredentials)->getRecipients();

    $startDate = CRM_Utils_Array::value('start_date', $params, CRM_Utils_Array::value('last_timestamp', $jobSettings));
    $endDate = CRM_Utils_Array::value('end_date', $params, date('Y-m-d H:i:s', strtotime(CRM_Utils_Array::value('omnimail_job_default_time_interval', $settings, ' + 1 day'), strtotime($startDate))));

    if (isset($jobSettings['retrieval_parameters'])) {
      // If there is an incomplete job get that.
      // @todo think about this a bit more - ie. we are ignoring date parameters here
      // and assuming it is a continuation
      $request->setRetrievalParameters($jobSettings['retrieval_parameters']);
    }
    elseif ($startDate) {
      $request->setStartTimeStamp(strtotime($startDate));
      $request->setEndTimeStamp(strtotime($endDate));
    }

    $result = $request->getResponse();
    for ($i = 0; $i < $settings['omnimail_job_retry_number']; $i++) {
      if ($result->isCompleted()) {
        $data = $result->getData();
        civicrm_api3('Setting', 'create', array(
          'omnimail_omnirecipient_load' => array(
            $params['mail_provider'] => array('last_timestamp' => $endDate),
          ),
        ));
        return $data;
      }
      else {
        sleep($settings['omnimail_job_retry_interval']);
      }
    }
    throw new CRM_Omnimail_IncompleteDownloadException('Download incomplete', 0, array(
      'retrieval_parameters' => $result->getRetrievalParameters(),
      'mail_provider' => $params['mail_provider'],
    ));

  }

  /**
   * @param $params
   * @return array
   */
  public static function getJobSettings($params) {
    $settings = self::getSettings();
    $jobSettings = CRM_Utils_Array::value($params['mail_provider'], $settings['omnimail_omnirecipient_load'], array());
    return $jobSettings;
  }

  /**
   * @return array|mixed
   */
  protected static function getSettings() {
    $settings = civicrm_api3('Setting', 'get', array('group' => 'omnimail'));
    $settings = reset($settings['values']);
    return $settings;
  }
}
