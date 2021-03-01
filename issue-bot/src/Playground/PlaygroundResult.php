<?php declare(strict_types = 1);

namespace App\Playground;

class PlaygroundResult
{

	private string $hash;

	/** @var string[] */
	private array $users;

	/** @var PlaygroundResultTab[] */
	private array $originalTabs;

	/** @var PlaygroundResultTab[] */
	private array $currentTabs;

	/**
	 * @param string[] $users
	 * @param PlaygroundResultTab[] $originalTabs
	 * @param PlaygroundResultTab[] $currentTabs
	 */
	public function __construct(
		string $hash,
		array $users,
		array $originalTabs,
		array $currentTabs
	)
	{
		$this->hash = $hash;
		$this->users = $users;
		$this->originalTabs = $originalTabs;
		$this->currentTabs = $currentTabs;
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

	/**
	 * @return PlaygroundResultTab[]
	 */
	public function getOriginalTabs(): array
	{
		return $this->originalTabs;
	}

	/**
	 * @return PlaygroundResultTab[]
	 */
	public function getCurrentTabs(): array
	{
		return $this->currentTabs;
	}

}
