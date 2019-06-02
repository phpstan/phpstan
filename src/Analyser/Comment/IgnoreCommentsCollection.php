<?php declare(strict_types = 1);

namespace PHPStan\Analyser\Comment;

final class IgnoreCommentsCollection
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
				$line = $node->getLine();

				if (
					$line < $ignoreComment->getStartLine() ||
					$line > $ignoreComment->getEndLine()
				) {
					return false;
				}

				if ($ignoreComment->shouldIgnoreNextLine()) {
					return true;
				}

				if (!$ignoreComment->isRegexp()) {
					return $message === $ignoreComment->getMessage();
				}

				preg_match(
					sprintf('/%s/', $ignoreComment->getMessage()),
					$message,
					$matches
				);

				return count($matches) > 0;
			}
		);

		return count($ignoredRules) > 0;
	}

}
