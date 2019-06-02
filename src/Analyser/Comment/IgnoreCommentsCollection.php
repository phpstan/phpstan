<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

class IgnoreCommentsCollection
{

	/** @var array<int, IgnoreComment> */
	private $ignoreComments = [];

	/** @var array<int, true> */
	private $usedIgnores = [];

	/**
	 * Add a rule to ignore
	 *
	 * @param IgnoreComment $ignoreComment
	 *
	 * @return void
	 */
	public function add(IgnoreComment $ignoreComment): void
	{
		$this->ignoreComments[] = $ignoreComment;
	}

	/**
	 * Check if a rule is to be ignored for a certain node
	 *
	 * @param \PhpParser\Node $node The node where the rule found an error
	 * @param string $message
	 *
	 * @return bool
	 */
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
