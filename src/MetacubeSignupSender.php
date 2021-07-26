<?php

namespace Creode\MarketingSignupMetacube;

use Creode\MarketingSignup\MarketingSignupSenderBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\RequestOptions;

class MetacubeSignupSender extends MarketingSignupSenderBase
{

  protected $accessToken = NULL;

  protected $expires = NULL;

  public function send($request_type, $endpoint, $data, $operations = []) {
    $this->validateAuth();

    // Testing stack
    $container = [];
    $history = Middleware::history($container);
    $stack = HandlerStack::create();
    $stack->push($history);

    $client = new Client([
      'handler' => $stack,
      'base_uri' => 'https://mcjfdlnbpvs8lj6lfcg3yssbczhy.rest.marketingcloudapis.com',
//      'base_uri' => 'https://example.com',
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $this->accessToken,
      ],
    ]);

    try {
      $response = $client->request(
        $request_type, $endpoint, /*$endpoint,*/
        [
          RequestOptions::JSON => $data,
          'debug' => TRUE,
        ]
      );
    }
    catch (ClientException $e) {
      echo $e->getResponse()->getBody()->getContents();
    }

    foreach ($container as $transaction) {
      echo (string) $transaction['request']->getBody();
    }

    return json_decode(
      $response->getBody()->getContents(),
      TRUE
    );
  }

  protected function validateAuth() {
    if (!is_null($this->accessToken) && $this->expires > time()) {
      return;
    }

    $this->authenticate();
  }

  protected function authenticate() {
    $client = new Client([
      'base_uri' => 'https://mcjfdlnbpvs8lj6lfcg3yssbczhy.auth.marketingcloudapis.com',
      'headers' => [
        'Content-Type' => 'application/json',
      ],
    ]);

    $data = $client->post('/v2/token', [
      RequestOptions::JSON => [
        'grant_type' => 'client_credentials',
        'client_id' => $this->api_arguments['client_id'],
        'client_secret' => $this->api_arguments['client_secret'],
        'scope' => NULL,
      ],
    ]);

    $accessData = json_decode(
      $data->getBody()->getContents(),
      TRUE
    );

    $this->accessToken = $accessData['access_token'];
    $this->expires = (time() + $accessData['expires_in']) - 2;
  }

}
