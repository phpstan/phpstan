<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

final class IgnoredRulesCollection
{

	/** @var array<array<string, string|int>> */
	private $ignoredRules = [];

	/**
	 * Add a rule to ignore
	 *
	 * @param \PhpParser\Node $node The node the rule belongs to
	 * @param string $ruleName
	 *
	 * @return void
	 */
	public function add(\PhpParser\Node $node, string $ruleName): void
	{
		$this->ignoredRules[] = [
			'startLine' => $node->getStartLine(),
			'endLine'   => $node->getEndLine(),
			'rule'      => $ruleName,
		];
	}

	/**
	 * Check if a rule is to be ignored for a certain node
	 *
	 * @param \PhpParser\Node $node The node where the rule found an error
	 * @param string $ruleName
	 *
	 * @return bool
	 */
	public function isIgnored(\PhpParser\Node $node, string $ruleName): bool
	{
		$ignoredRules = array_filter(
			$this->ignoredRules,
			static function (array $ignoredRule) use ($node, $ruleName): bool {
				$line = $node->getLine();

				return $ignoredRule['rule'] === $ruleName &&
					$line >= $ignoredRule['startLine'] &&
					$line <= $ignoredRule['endLine'];
			}
		);

		return count($ignoredRules) > 0;
	}

}
