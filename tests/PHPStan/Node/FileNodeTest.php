<?php declare(strict_types = 1);

namespace PHPStan\Node;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\File\SimpleRelativePathHelper;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Testing\RuleTestCase;

class FileNodeTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new class() implements Rule {

			public function getNodeType(): string
			{
				return FileNode::class;
			}

			/**
			 * @param \PHPStan\Node\FileNode $node
			 * @param \PHPStan\Analyser\Scope $scope
			 * @return \PHPStan\Rules\RuleError[]
			 */
			public function processNode(Node $node, Scope $scope): array
			{
				$nodes = $node->getNodes();
				$pathHelper = new SimpleRelativePathHelper(__DIR__ . '/data');
				if (!isset($nodes[0])) {
					return [
						RuleErrorBuilder::message(sprintf('File %s is empty.', $pathHelper->getRelativePath($scope->getFile())))->line(1)->build(),
					];
				}

				return [
					RuleErrorBuilder::message(
						sprintf('First node in file %s is: %s', $pathHelper->getRelativePath($scope->getFile()), get_class($nodes[0]))
					)->build(),
				];
			}

		};
	}

	public function dataRule(): iterable
	{
		yield [
			__DIR__ . '/data/empty.php',
			'File empty.php is empty.',
			1,
		];

		yield [
			__DIR__ . '/data/declare.php',
			'First node in file declare.php is: PhpParser\Node\Stmt\Declare_',
			1,
		];

		yield [
			__DIR__ . '/data/namespace.php',
			'First node in file namespace.php is: PhpParser\Node\Stmt\Namespace_',
			3,
		];
	}

	/**
	 * @dataProvider dataRule
	 * @param string $file
	 * @param string $expectedError
	 * @param int $line
	 */
	public function testRule(string $file, string $expectedError, int $line): void
	{
		$this->analyse([$file], [
			[
				$expectedError,
				$line,
			],
		]);
	}

}
