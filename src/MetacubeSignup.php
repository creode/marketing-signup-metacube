<?php

namespace Creode\MarketingSignupMetacube;

use Creode\MarketingSignup\MarketingSignupTypeBase;

class MetacubeSignup extends MarketingSignupTypeBase
{

  public function constructSender($api_arguments = []) {
    return new MetacubeSignupSender($api_arguments);
  }

  /**
   * @inheritdoc
   */
  public function getName() {
    return 'Metacube';
  }

  /**
   * @inheritdoc
   */
  protected function requiredFieldKeys() {
    return [
      'Email_Address'
    ];
  }

  /**
   * @inheritdoc
   */
  protected function optionalFieldKeys() {
    return [
      "First_Name",
      "Last_Name",
      "Email_Address",
      "I agree to receive occasional emails from Sipsmith",
      "Opt_In_Competition",
      "Source",
      "Opt_In_Time",
      "Country",
    ];
  }

  /**
   * @inheritdoc
   */
  public function add($extra_data = []) {
    if (!$this->list_id) {
      throw new \Exception(
        'No List identifier provided for mailchimp. Please add `list_id` property with a valid list id.'
      );
    }

    if (!$this->validate()) {
      throw new \Exception(
        'Required fields for this API are either missing or in the wrong format. Please check them in the MailChimp Documentation.'
      );
    }

    return $this->sender->send(
      'POST',
      "/interaction/v1/events",
      [
        'ContactKey' => $this->data['Email_Address'],
        'EventDefinitionKey' => $this->list_id,
        'Data' => $this->data,
      ]
    );
  }

}
