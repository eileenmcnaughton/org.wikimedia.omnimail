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

}