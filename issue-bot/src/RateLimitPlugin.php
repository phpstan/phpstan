<?php declare(strict_types = 1);

namespace App;

use Github\Api\RateLimit;
use Github\Api\RateLimit\RateLimitResource;
use Github\Client;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class RateLimitPlugin implements Plugin
{

	private Client $client;

	public function setClient(Client $client): void
	{
		$this->client = $client;
	}

	public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
	{
		$path = $request->getUri()->getPath();
		if ($path === '/rate_limit') {
			return $next($request);
		}

		/** @var RateLimit $api */
		$api = $this->client->api('rate_limit');

		/** @var RateLimitResource $resource */
		$resource = $api->getResource('core');
		if ($resource->getRemaining() < 10) {
			$reset = $resource->getReset();
			$sleepFor = $reset - time();
			if ($sleepFor > 0) {
				echo sprintf("Rate limit exceeded - sleeping for %d seconds\n", $sleepFor);
				sleep($sleepFor);
			}
		}

		return $next($request);
	}

}
