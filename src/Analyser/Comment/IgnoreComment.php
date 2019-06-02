<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

use PhpParser\Comment;
use PhpParser\Node;

class IgnoreComment
{

	/** @var Comment */
	private $comment;

	/** @var Node */
	private $node;

	/** @var bool */
	private $ignoreNextLine;

	/**  @var string */
	private $message;

	/** @var bool */
	private $isRegexp;

	public function __construct(
		Comment $comment,
		Node $node,
		bool $ignoreNextLine,
		string $message = '',
		bool $isRegexp = false
	)
	{
		$this->comment = $comment;
		$this->node = $node;
		$this->ignoreNextLine = $ignoreNextLine;
		$this->message = $message;
		$this->isRegexp = $isRegexp;
	}

	public function getComment(): Comment
	{
		return $this->comment;
	}

	public function getStartLine(): int
	{
		return $this->node->getStartLine();
	}

	public function getEndLine(): int
	{
		return $this->node->getEndLine();
	}

	public function shouldIgnoreNextLine(): bool
	{
		return $this->ignoreNextLine;
	}

	public function isRegexp(): bool
	{
		return $this->isRegexp;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

}
