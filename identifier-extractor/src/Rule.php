<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Rules\RuleErrorBuilder;
use function array_unique;
use function array_values;

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

		$injected = [];
		foreach ($node->get(RuleConstructorParameterCollector::class) as $rows) {
			foreach ($rows as $row) {
				foreach ($row['parameters'] as $parameter) {
					$injected[$parameter][] = $row['class'];
				}
			}
		}

		foreach ($injected as $key => $values) {
			$injected[$key] = array_values(array_unique($values));
		}

		var_dump($injected);

		foreach ($node->get(RuleErrorBuilderCollector::class) as $rows) {
			foreach ($rows as $row) {
				$errors[] = RuleErrorBuilder::message('Metadata')
					->identifier('phpstanIdentifierExtractor.data')
					->metadata($row)
					->build();
			}
		}

		foreach ($node->get(ErrorWithIdentifierCollector::class) as $rows) {
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
