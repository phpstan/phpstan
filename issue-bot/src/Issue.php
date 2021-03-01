<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundExample;
use DateTimeImmutable;

class Issue
{

	private int $number;

	private string $author;

	private string $text;

	private DateTimeImmutable $updatedAt;

	/** @var Comment[] */
	private iterable $comments;

	/** @var PlaygroundExample[] */
	private array $playgroundExamples;

	/**
	 * @param Comment[] $comments
	 * @param PlaygroundExample[] $playgroundExamples
	 */
	public function __construct(
		int $number,
		string $author,
		string $text,
		DateTimeImmutable $updatedAt,
		iterable $comments,
		array $playgroundExamples
	)
	{
		$this->number = $number;
		$this->author = $author;
		$this->text = $text;
		$this->updatedAt = $updatedAt;
		$this->comments = $comments;
		$this->playgroundExamples = $playgroundExamples;
	}

	public function getNumber(): int
	{
		return $this->number;
	}

	public function getAuthor(): string
	{
		return $this->author;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function getUpdatedAt(): DateTimeImmutable
	{
		return $this->updatedAt;
	}

	/**
	 * @return Comment[]
	 */
	public function getComments(): iterable
	{
		return $this->comments;
	}

	/**
	 * @return PlaygroundExample[]
	 */
	public function getPlaygroundExamples(): array
	{
		return $this->playgroundExamples;
	}

}
