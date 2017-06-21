<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 5/3/17
 * Time: 12:46 PM
 */
// Include the library
require_once 'vendor/autoload.php';

/**
 * Get details about Recipients.
 *
 * @param $params
 *
 * @return array
 */
function civicrm_api3_omnirecipient_load($params) {
  $recipients = CRM_Omnimail_Omnirecipients::getResult($params);

      foreach ($recipients as $recipient) {
        $insertValues = array(
          1 => array((string) $recipient->getContactIdentifier(), 'String'),
          2 => array((string) CRM_Utils_Array::value('mailing_prefix', $params, '') . $recipient->getMailingIdentifier(), 'String'),
          3 => array((string) $recipient->getEmail(), 'String'),
          4 => array((string) $recipient->getRecipientAction(), 'String'),
          5 => array((string) $recipient->getRecipientActionIsoDateTime(), 'String'),
          6 => array((string) $recipient->getContactReference(), 'String'),
        );
       CRM_Core_DAO::executeQuery("
         INSERT IGNORE INTO civicrm_mailing_provider_data
         (`contact_identifier`, `mailing_identifier`, `email`, `event_type`, `recipient_action_datetime`, `contact_id`)
         values(%1, %2, %3, %4, %5, %6 )",
         $insertValues);
      }

      /* This requires a permission not held on staging.
      $query = '
    LOAD DATA INFILE "' . $csvFile . '"
    INTO TABLE my_table
    FIELDS TERMINATED BY ","
    OPTIONALLY ENCLOSED BY \'"\'
    ESCAPED BY "\\\\"
    LINES TERMINATED BY "\n"
    IGNORE 1 LINES
    (`' . implode('`,`', $columns) . '`)';
      CRM_Core_DAO::executeQuery($query);
      */

      return civicrm_api3_create_success(1);

}

/**
 * Get details about Omnimails.
 *
 * @param $params
 */
function _civicrm_api3_omnirecipient_load_spec(&$params) {
  $params['username'] = array(
    'title' => ts('User name'),
  );
  $params['password'] = array(
    'title' => ts('Password'),
  );
  $params['mail_provider'] = array(
    'title' => ts('Name of Mailer'),
    'api.required' => TRUE,
  );
  $params['start_date'] = array(
    'title' => ts('Date to fetch from'),
    'type' => CRM_Utils_Type::T_TIMESTAMP,
  );
  $params['end_date'] = array(
    'title' => ts('Date to fetch to'),
    'type' => CRM_Utils_Type::T_TIMESTAMP,
  );
  $params['mailing_external_identifier'] = array(
    'title' => ts('Identifier for the mailing'),
    'type' => CRM_Utils_Type::T_STRING,
  );
  $params['retrieval_parameters'] = array(
    'title' => ts('Additional information for retrieval of pre-stored requests'),
  );
  $params['table_name'] = array(
    'title' => 'Name of table to store to',
    'type' => CRM_Utils_Type::T_STRING,
  );

}
