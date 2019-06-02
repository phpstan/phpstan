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

	public function ignores(Node $node, string $message): bool
	{
		$line = $node->getLine();

		if (
			$line < $this->node->getStartLine() ||
			$line > $this->node->getEndLine()
		) {
			return false;
		}

		if ($this->ignoreNextLine) {
			return true;
		}

		if (!$this->isRegexp) {
			return $message === $this->message;
		}

		preg_match(
			sprintf('/%s/', $this->message),
			$message,
			$matches
		);

		return count($matches) > 0;
	}

	public function describe(): string
	{
		if ($this->ignoreNextLine) {
			return sprintf('There is no error to ignore on %s.', $this->describeLines());
		}

		if ($this->isRegexp) {
			return sprintf('There is no error matching regular expression "%s" on %s.', $this->message, $this->describeLines());
		}

		return sprintf('There is no error "%s" on %s.', $this->message, $this->describeLines());
	}

	private function describeLines(): string
	{
		$startLine = $this->node->getStartLine();
		$endLine = $this->node->getEndLine();

		if ($startLine === $endLine) {
			return 'the next line';
		}

		return sprintf('lines %d-%d', $startLine, $endLine);
	}

	public function getLine(): int
	{
		return $this->comment->getLine();
	}

}
