<?hh //strict

namespace Etsy\Validators;

use Plenty\Validation\Validator;

class EtsyReceiptValidator extends Validator
{
    protected function defineAttributes():void
    {
		$this->addInt('receipt_id', true);
		$this->addInt('order_id', true);
		$this->addInt('buyer_user_id', true);

        $this->addString('name', true);
        $this->addString('first_line', true);
        $this->addString('city', true);
        $this->addConditional('zip', true);
        $this->addConditional('Transactions', true)->isArray()->min(1);
        $this->addString('buyer_email', true)->email();


		// TODO add here all other fields that etsy needs to return us in order to save the receipt as an plenty order.
    }
}
