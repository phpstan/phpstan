<?php declare(strict_types = 1);

namespace PHPStan\Generics;

class GenericsIntegrationTest extends \PHPStan\Testing\LevelsTestCase
{

	public function dataTopics(): array
	{
		return [
			['functions'],
			['functionsAssertType'],
			['invalidReturn'],
			['pick'],
			['varyingAcceptor'],
		];
	}

	public function getDataPath(): string
	{
		return __DIR__ . '/data';
	}

	public function getPhpStanExecutablePath(): string
	{
		return __DIR__ . '/../../../bin/phpstan';
	}

	public function getPhpStanConfigPath(): ?string
	{
		return __DIR__ . '/generics.neon';
	}

}
