<?php declare(strict_types = 1);

namespace App;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class RequestCounterPlugin implements Plugin
{

	private int $totalCount = 0;

	public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
	{
		$path = $request->getUri()->getPath();
		if ($path === '/rate_limit') {
			return $next($request);
		}

		$this->totalCount++;
		return $next($request);
	}

	public function getTotalCount(): int
	{
		return $this->totalCount;
	}

}
