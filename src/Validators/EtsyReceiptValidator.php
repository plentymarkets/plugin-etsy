<?php

namespace Etsy\Validators;

use Plenty\Validation\Validator;

/**
 * Class EtsyReceiptValidator
 */
class EtsyReceiptValidator extends Validator
{
	/**
	 * @return void
	 */
	protected function defineAttributes()
	{
		$this->addInt('receipt_id', true);
		$this->addInt('order_id', true);
		$this->addInt('buyer_user_id', true);

		$this->addString('name', true);
		$this->addString('first_line', true);
		$this->addString('city', true);
		$this->addConditional('Transactions', true)->isArray()->min(1);
		$this->addString('buyer_email', true)->email();
	}
}
