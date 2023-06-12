<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<CollectedDataNode>
 */
class Rule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return CollectedDataNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];

		foreach ($node->get(RuleErrorBuilderCollector::class) as $rows) {
			foreach ($rows as $row) {
				$errors[] = RuleErrorBuilder::message('Metadata')
					->identifier('phpstanIdentifierExtractor.data')
					->metadata($row)
					->build();
			}
		}

		return $errors;
	}

}
