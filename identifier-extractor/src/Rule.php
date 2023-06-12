<?php declare(strict_types = 1);

namespace IdentifierExtractor;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\CollectedDataNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function array_pop;
use function array_unique;
use function array_values;
use function in_array;

/**
 * @implements \PHPStan\Rules\Rule<CollectedDataNode>
 */
class Rule implements \PHPStan\Rules\Rule
{

	public function __construct(private ReflectionProvider $reflectionProvider)
	{
	}

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

		foreach ($node->get(RuleErrorBuilderCollector::class) as $rows) {
			foreach ($this->processRows($rows, $injected) as $error) {
				$errors[] = $error;
			}
		}

		foreach ($node->get(ErrorWithIdentifierCollector::class) as $rows) {
			foreach ($this->processRows($rows, $injected) as $error) {
				$errors[] = $error;
			}
		}

		return $errors;
	}

	/**
	 * @param list<array{identifiers: non-empty-list<string>, class: string, file: string, line: int}> $rows
	 * @param array<string, list<string>> $injected
	 * @return list<IdentifierRuleError>
	 */
	private function processRows(array $rows, array $injected): array
	{
		$errors = [];
		foreach ($rows as $row) {
			foreach ($this->findOrigins($row['class'], $injected) as $origin) {
				$errors[] = RuleErrorBuilder::message('Metadata')
					->identifier('phpstanIdentifierExtractor.data')
					->metadata([
						'identifiers' => $row['identifiers'],
						'class' => $origin,
						'file' => $row['file'],
						'line' => $row['line'],
					])->build();
			}
		}

		return $errors;
	}

	/**
	 * @param array<string, list<string>> $injected
	 * @return list<string>
	 */
	private function findOrigins(string $class, array $injected): array
	{
		$origins = [];
		$stack = [$class];
		while (count($stack) > 0) {
			$item = array_pop($stack);
			if ($this->reflectionProvider->hasClass($item)) {
				$reflection = $this->reflectionProvider->getClass($item);
				if ($reflection->implementsInterface(\PHPStan\Rules\Rule::class)) {
					$origins[] = $item;
				}
			}

			if (!isset($injected[$item])) {
				continue;
			}

			foreach ($injected[$item] as $v) {
				if (in_array($v, $stack, true)) {
					continue;
				}
				$stack[] = $v;
			}
		}

		return $origins;
	}

}
