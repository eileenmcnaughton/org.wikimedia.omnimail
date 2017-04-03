<?php

use Omnimail\Silverpop\Responses\RecipientsResponse;
use Omnimail\Omnimail;

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
   * @param $params
   * @return RecipientsResponse
   */
  static function getResult($params) {
    $settings = civicrm_api3('Setting', 'get', array('group' => 'omnimail'));
    $settings = reset($settings['values']);
    $jobSettings = CRM_Utils_Array::value($params['mail_provider'], $settings['omnimail_omnirecipient_load'], array());

    $request = Omnimail::create($params['mail_provider'], array(
      'Username' => $params['username'],
      'Password' => $params['password']
    ))->getRecipients();

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
    civicrm_api3('Setting', 'create', array(
      'omnimail_omnirecipient_load' => array(
        $params['mail_provider'] => array(
          // Don't update our last timestamp until it has worked.
          'last_timestamp' => $startDate,
          'retrieval_parameters' => $result->getRetrievalParameters(),
        ),
      ),
    ));

  }
}
