<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundExample;

class BotComment extends Comment
{

	private string $resultHash;

	private string $diff;

	public function __construct(
		string $text,
		PlaygroundExample $playgroundExample,
		string $diff
	)
	{
		parent::__construct('phpstan-bot', $text, [$playgroundExample]);
		$this->resultHash = $playgroundExample->getHash();
		$this->diff = $diff;
	}

	public function getResultHash(): string
	{
		return $this->resultHash;
	}

	public function getDiff(): string
	{
		return $this->diff;
	}

}
