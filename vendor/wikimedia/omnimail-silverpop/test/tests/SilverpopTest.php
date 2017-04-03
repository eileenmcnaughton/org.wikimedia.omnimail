<?php

namespace Omnimail\Silverpop\Tests;

use Omnimail\Omnimail;
use Omnimail\Silverpop\Responses\RecipientsResponse;
use Omnimail\Silverpop\Responses\MailingsResponse;
use Omnimail\Silverpop\Tests\BaseTestClass;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class SilverpopTest extends BaseTestClass {

    /**
     * Test retrieving mailings.
     */
    public function testGetMailings() {
        $requests = array(file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'));
        /* @var $request \Omnimail\Silverpop\Requests\RawRecipientDataExportRequest */
        $request = Omnimail::create('Silverpop', array('client' => $this->getMockRequest($requests)))->getMailings();
        $response = $request->getResponse();
        $this->assertTrue(is_a($response, 'Omnimail\Silverpop\Responses\MailingsResponse'));

    }

  /**
   * Get mock guzzle client object.
   *
   * @param array $container
   * @param $body
   * @param bool $authenticateFirst
   * @return \GuzzleHttp\Client
   */
    public function getMockRequest($body = array(), $authenticateFirst = TRUE) {

      $responses = array();
      if ($authenticateFirst) {
        $responses[] = new Response(200, [], file_get_contents(__DIR__ . '/Responses/AuthenticateResponse.txt'));
      }
      foreach ($body as $responseBody) {
        $responses[] = new Response(200, [], $responseBody);
      }
      $mock = new MockHandler($responses);
      $handler = HandlerStack::create($mock);
      return new Client(array('handler' => $handler));
    }

}
