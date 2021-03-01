<?php declare(strict_types = 1);

namespace App\Playground;

use GuzzleHttp\Promise\PromiseInterface;

class PlaygroundExample
{

	private string $url;

	private string $hash;

	/** @var string[] */
	private array $users;

	private PromiseInterface $resultPromise;

	public function __construct(
		string $url,
		string $hash,
		string $user,
		PromiseInterface $resultPromise
	)
	{
		$this->url = $url;
		$this->hash = $hash;
		$this->users = [$user];
		$this->resultPromise = $resultPromise;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * @return string[]
	 */
	public function getUsers(): array
	{
		return $this->users;
	}

	public function addUser(string $user): void
	{
		$users = $this->users;
		$users[] = $user;
		$this->users = array_values(array_unique($users));
	}

	public function getResultPromise(): PromiseInterface
	{
		return $this->resultPromise;
	}

}
