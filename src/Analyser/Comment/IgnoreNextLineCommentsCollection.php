<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

class IgnoreNextLineCommentsCollection
{

	/** @var array<int, IgnoreNextLineComment> */
	private $ignoreComments = [];

	/** @var array<int, true> */
	private $usedIgnores = [];

	public function add(IgnoreNextLineComment $ignoreComment): void
	{
		$this->ignoreComments[] = $ignoreComment;
	}

	public function isIgnored(\PhpParser\Node $node, string $message): bool
	{
		foreach ($this->ignoreComments as $i => $ignoreComment) {
			if (!$ignoreComment->ignores($node, $message)) {
				continue;
			}

			$this->usedIgnores[$i] = true;
			return true;
		}

		return false;
	}

	/**
	 * @return array<IgnoreComment>
	 */
	public function getUnusedIgnores(): array
	{
		$comments = [];
		foreach ($this->ignoreComments as $i => $ignoreComment) {
			if (isset($this->usedIgnores[$i])) {
				continue;
			}

			$comments[] = $ignoreComment;
		}

		return $comments;
	}

}
