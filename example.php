<?php

require __DIR__ . '/vendor/autoload.php';

use Creode\MarketingSignupMetacube\MetacubeSignup;

$data = [
  'First_Name' => 'Prince',
  'Last_Name' => 'Arora',
  'Email_Address' => 'prince.arora.metacube@beamsuntory.com',
  'I agree to receive occasional emails from Sipsmith' => TRUE,
  'Opt_In_Competition' => '23543',
  'Source' => 'Web',
  // Format the date as expected (eg '06/30/2021 17:47')
  'Opt_In_Time' => (new DateTime())->format('m/d/Y H:i'),
  'Country' => 'uk',
];

$event_definition_key = 'APIEvent-d6ab530d-6903-12de-afad-e27566b07ecc';

$signup = new MetacubeSignup(
  $data,
  [
    'client_id' => '<MY_CLIENT_ID>',
    'client_secret' => '<MY_CLIENT_SECRET>',
    'base_uri' => '<API_BASE_URI>',
  ],
  $event_definition_key
);
$result = $signup->add();

var_dump($result);
