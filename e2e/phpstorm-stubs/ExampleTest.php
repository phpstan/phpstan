<?php

namespace PHPStanE2EJetbrainsPhpStormStubs;

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpParser\Node\Stmt\Echo_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Testing\RuleTestCase;

class ExampleTest extends RuleTestCase
{

	protected function getRule(): \PHPStan\Rules\Rule
	{
		return new class($this->createReflectionProvider()) implements Rule {

			/** @var ReflectionProvider */
			private $reflectionProvider;

			public function __construct(ReflectionProvider $reflectionProvider)
			{
				$this->reflectionProvider = $reflectionProvider;
			}

			/**
			 * @return string
			 */
			public function getNodeType(): string
			{
				return Echo_::class;
			}

			public function processNode(\PhpParser\Node $node, Scope $scope): array
			{
				$this->reflectionProvider->getClass('LevelDBWriteBatch');

				return [
                    RuleErrorBuilder::message(sprintf('Echo: %s', $node->exprs[0]->value))
                        ->identifier('phpstormStubs.e2e')
                        ->build(),
				];
			}
		};
	}

	public function testRule(): void
	{
		$this->analyse([__DIR__ . '/test.php'], [
			[
				'Echo: ok',
				3,
			],
		]);
	}

}
