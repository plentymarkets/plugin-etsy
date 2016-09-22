<?hh //strict
namespace Etsy\Api\Services;

use Etsy\Logger\Logger;
use Etsy\Api\Client;

class ReceiptService
{
    private Client $client;

    private Logger $logger;

    public function __construct(
        Client $client,
        Logger $logger
    )
    {
        $this->client = $client;
        $this->logger = $logger;
    }

	public function findAllShopReceipts(int $shopId, string $from, string $to):mixed
	{
		$response = $this->client->call('findAllShopReceipts',
			[
				'shop_id' => $shopId,
				'min_created' => $from,
				'max_created' => $to,
			],
			[],
			[],
			[
				'Transactions' => 'Transactions',
				'Buyer' => 'Buyer',
			]
		);

        if(!is_null($response) && is_array($response['results']))
        {
            return $response['results'];
        }
        else
        {
            return [];
        }
	}
}
