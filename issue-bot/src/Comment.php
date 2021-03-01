<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundExample;

class Comment
{

	private string $author;

	private string $text;

	/** @var PlaygroundExample[] */
	private array $playgroundExamples;

	/**
	 * @param PlaygroundExample[] $playgroundExamples
	 */
	public function __construct(
		string $author,
		string $text,
		array $playgroundExamples
	)
	{
		$this->author = $author;
		$this->text = $text;
		$this->playgroundExamples = $playgroundExamples;
	}

	public function getAuthor(): string
	{
		return $this->author;
	}

	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * @return PlaygroundExample[]
	 */
	public function getPlaygroundExamples(): array
	{
		return $this->playgroundExamples;
	}

}
