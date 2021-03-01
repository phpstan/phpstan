<?php declare(strict_types = 1);

namespace App\Playground;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;

class PlaygroundClient
{

	private Client $client;

	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	public function getResultPromise(string $hash, string $user): PromiseInterface
	{
		$response = $this->client->getAsync(
			sprintf('https://api.phpstan.org/result?id=%s', $hash)
		);

		return $response->then(static function (ResponseInterface $response) use ($hash, $user): PlaygroundResult {
			$body = (string) $response->getBody();
			$json = Json::decode($body, Json::FORCE_ARRAY);

			$convert = static function (array $tab): PlaygroundResultTab {
				return new PlaygroundResultTab(
					$tab['title'],
					array_map(static function (array $error): PlaygroundResultError {
						return new PlaygroundResultError($error['message'], $error['line'] ?? -1);
					}, $tab['errors'])
				);
			};

			return new PlaygroundResult(
				$hash,
				[$user],
				array_map($convert, $json['tabs'] ?? [[
					'title' => 'PHP 7.4',
					'errors' => $json['errors'],
				]]),
				array_map($convert, $json['upToDateTabs'])
			);
		});
	}

}
