<?php

namespace Omnimail\Silverpop\Connector;

use Omnimail\Silverpop\Connector\Xml\GetAggregateTrackingForMailing;
use Omnimail\Silverpop\Connector\Xml\CalculateQuery;
use Omnimail\Silverpop\Connector\Xml\GetMailingTemplate;
use SilverpopConnector\SilverpopRestConnector;
use SilverpopConnector\SilverpopXmlConnector;
use SilverpopConnector\SilverpopConnectorException;
use SimpleXmlElement;
use phpseclib\Net\Sftp;
use GuzzleHttp\Client;

/**
 * This is a basic class for connecting to the Silverpop XML API. If you
 * need to connect only to the XML API, you can use this class directly.
 * However, if you would like to utilize resources spread between the XML
 * and REST APIs, you shoudl instead use the generalized SilverpopConnector
 * class.
 *
 * @author Mark French, Argyle Social
 */
class SilverpopGuzzleXmlConnector extends SilverpopXmlConnector {
    protected static $instance = null;

    /**
     * Performs Silverpop Logout so concurrent requests can take place
     *
     * @throws SilverpopConnectorException
     */
    public function logout() {
        $params = "<Envelope>\n\t<Body>\n\t\t<Logout/>\n\t</Body></Envelope>";

        $ch = curl_init();
        $curlParams = array(
            CURLOPT_URL            => $this->baseUrl.'/XMLAPI',
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => http_build_query(array('xml'=>$params)),
        );
        $set = curl_setopt_array($ch, $curlParams);

        $resultStr = curl_exec($ch);
        curl_close($ch);
        $result = $this->checkResponse($resultStr);

        $this->sessionId = null;
    }

    /**
     * Get mailing template.
     *
     * @param array $params
     *
     * @return SimpleXmlElement
     */
    public function getMailingTemplate($params) {
        $template = new GetMailingTemplate($params);
        $params = $template->getXml();
        $result = $this->post($params);
        return $template->formatResult($result);
    }

    /**
     * Calculate the Current Contacts for a Query
     *
     * This interface supports programmatically calculating the number of
     * contacts for a query. A data job is submitted to calculate the query
     * and GetJobStatus must be used to determine whether the data job is complete.
     *
     * You may only call the Calculate Query data job for a particular query if
     * you have not calculated the query size in the last 12 hours.
     *
     * @param $params
     *  - queryId int ID of the query or list you wish to retrieve.
     *  - email string Email to notify on success (optional).
     *
     * @return array
     */
    public function calculateQuery($params) {
        $template = new CalculateQuery($params);
        $params = $template->getXml();
        $result = $this->post($params);
        return $template->formatResult($result);
    }

    /**
     * Get aggregate tracking information for a mailing.
     *
     * This includes summary data about the number sent etc.
     *
     * @param array $params
     * @return SimpleXmlElement
     */
    public function getAggregateTrackingForMailing($params) {
        $template = new GetAggregateTrackingForMailing($params);
        $params = $template->getXml();
        $result = $this->post($params);
        return $template->formatResult($result);
    }
    
}
