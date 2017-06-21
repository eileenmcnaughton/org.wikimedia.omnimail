<?php
/**
 * Created by IntelliJ IDEA.
 * User: emcnaughton
 * Date: 6/21/17
 * Time: 11:30 AM
 */

namespace Omnimail\Common;

/**
 * Class Credentials
 *
 * Object for storing credentials. This is excluded from debug output for privacy.
 *
 * This class has some further ideas for protecting data
 *
 * https://github.com/Payum/Payum/blob/master/src/Payum/Core/Security/SensitiveValue.php
 *
 * @package Omnimail\Common
 */
class Credentials implements CredentialInterface
{
    private $credentials = array();

    /**
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Magic method http://php.net/manual/en/language.oop5.magic.php#object.debuginfo
     *
     * We override this to ensure that the credentials are
     * excluded from var_dump.
     */
    public function __debugInfo() {}

}