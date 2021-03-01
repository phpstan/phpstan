<?php declare(strict_types = 1);

namespace App\Playground;

class PlaygroundResultTab
{

	private string $title;

	/** @var PlaygroundResultError[] */
	private array $errors;

	/**
	 * @param string $title
	 * @param PlaygroundResultError[] $errors
	 */
	public function __construct(string $title, array $errors)
	{
		$this->title = $title;
		$this->errors = $errors;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return PlaygroundResultError[]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

}
