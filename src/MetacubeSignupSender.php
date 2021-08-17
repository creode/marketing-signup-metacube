<?php

namespace Creode\MarketingSignupMetacube;

use Creode\MarketingSignup\AuthenticationException;
use Creode\MarketingSignup\DuplicateKeyException;
use Creode\MarketingSignup\InvalidArgumentException;
use Creode\MarketingSignup\MarketingSignupSenderBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

class MetacubeSignupSender extends MarketingSignupSenderBase
{

  protected $accessToken = NULL;

  protected $expires = NULL;

  public function send($request_type, $endpoint, $data, $operations = []) {
    $this->validateAuth();

    $baseUri = str_replace('.auth.', '.rest.', $this->api_arguments['base_uri']);

    $client = new Client([
      'base_uri' => $baseUri,
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $this->accessToken,
      ],
    ]);

    try {
      $response = $client->request(
        $request_type,
        $endpoint,
        [
          RequestOptions::JSON => $data,
          'debug' => TRUE,
        ]
      );
    }
    catch (ClientException $e) {
    	$exceptionBody = json_decode(
    		$e->getResponse()->getBody()->getContents(),
		    TRUE
	    );
    	switch ($e->getResponse()->getStatusCode()) {
		    case 400:
		    	if ($exceptionBody['message'] === 'The event data contains duplicate value for an existing primary key. Please correct the event data and try again.') {
		    		throw new DuplicateKeyException($exceptionBody['message']);
			    }
		    	throw new InvalidArgumentException($exceptionBody['message']);

		    case 401:
		    	throw new AuthenticationException($exceptionBody['message']);

		    default:
		    	throw $e;
	    }
    }
    catch (ServerException $e) {
    	throw $e;
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
      'base_uri' => $this->api_arguments['base_uri'],
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
