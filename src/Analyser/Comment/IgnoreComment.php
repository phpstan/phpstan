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

	/**  @var string|null */
	private $message;

	/** @var bool */
	private $isRegexp;

	private function __construct(
		Comment $comment,
		Node $node,
		bool $ignoreNextLine,
		?string $message,
		bool $isRegexp
	)
	{
		$this->comment = $comment;
		$this->node = $node;
		$this->ignoreNextLine = $ignoreNextLine;
		$this->message = $message;
		$this->isRegexp = $isRegexp;
	}

	public static function createIgnoreNextLine(Comment $comment, Node $node): self
	{
		return new self($comment, $node, true, null, false);
	}

	public static function createIgnoreMessage(Comment $comment, Node $node, string $message): self
	{
		return new self($comment, $node, false, $message, false);
	}

	public static function createIgnoreRegexp(Comment $comment, Node $node, string $pattern): self
	{
		return new self($comment, $node, false, $pattern, true);
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

	public function getMessage(): ?string
	{
		return $this->message;
	}

}
