<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

class IgnoreCommentsCollection
{

	/** @var array<IgnoreComment> */
	private $ignoredRules = [];

	/**
	 * Add a rule to ignore
	 *
	 * @param IgnoreComment $ignoreComment
	 *
	 * @return void
	 */
	public function add(IgnoreComment $ignoreComment): void
	{
		$this->ignoredRules[] = $ignoreComment;
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
		$ignoredRules = array_filter(
			$this->ignoredRules,
			static function (IgnoreComment $ignoreComment) use ($node, $message): bool {
				return $ignoreComment->ignores($node, $message);
			}
		);

		return count($ignoredRules) > 0;
	}

}
