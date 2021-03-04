<?php declare(strict_types = 1);

namespace App;

use GuzzleHttp\Promise\PromiseInterface;

class PromiseResolver
{

	/** @var PromiseInterface[] */
	private array $promises = [];

	private int $counter = 0;

	private int $totalCount = 0;

	public function push(PromiseInterface $promise, int $count): void
	{
		$this->promises[] = $promise;
		$this->counter += $count;
		$this->totalCount += $count;
		if ($this->counter < 25) {
			return;
		}

		$this->flush();
	}

	public function flush(): void
	{
		$promises = $this->promises;
		$this->promises = [];
		$this->counter = 0;
		\GuzzleHttp\Promise\Utils::all($promises)->wait();
	}

	public function getTotalCount(): int
	{
		return $this->totalCount;
	}

}
