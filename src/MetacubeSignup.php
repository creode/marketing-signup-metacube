<?php

namespace Creode\MarketingSignupMetacube;

use Creode\MarketingSignup\MarketingSignupTypeBase;

class MetacubeSignup extends MarketingSignupTypeBase {
	/**
	 * Constructs the sender object.
	 *
	 * @param array $api_arguments Array of arguments for the API.
	 */
	public function constructSender( $api_arguments = array() ) {
		return new MetacubeSignupSender( $api_arguments );
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
		return array(
			'EmailAddress',
		);
	}

	/**
	 * @inheritdoc
	 */
	protected function optionalFieldKeys() {
		return array(
			'FirstName',
			'LastName',
			'EmailAddress',
			'Country',
			'StateProvince',
			'City',
			'PostalCode',
			'Birthdate',
			'OptInLegal',
			'OptInMarketing',
			'Source',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function add( $extra_data = [] ) {
		if ( ! $this->list_id ) {
			throw new \Exception(
				'No Event provided for Metacube. Please add `event` property with a valid id.'
			);
		}

		if ( ! $this->validate() ) {
			throw new \Exception(
				'Required fields for this API are either missing or in the wrong format. Please check them in the MetaCube Documentation.'
			);
		}

		return $this->sender->send(
			'POST',
			"/interaction/v1/events",
			[
				'ContactKey' => $this->data['EmailAddress'],
				'EventDefinitionKey' => $this->list_id,
				'Data' => $this->data,
			]
		);
	}

}
