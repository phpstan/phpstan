<?php declare(strict_types = 1);

namespace PHPStan\Levels;

class InferPrivatePropertyTypeFromConstructorIntegrationTest extends \PHPStan\Testing\LevelsTestCase
{

	public function dataTopics(): array
	{
		return [
			['inferPropertyType'],
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
		return __DIR__ . '/inferPropertyType.neon';
	}

}
