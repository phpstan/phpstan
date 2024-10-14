<?php declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

class PharTest extends \PHPStan\Testing\LevelsTestCase
{

	public static function dataTopics(): array
	{
		return [
			['strictRulesExtension'],
		];
	}

	public function getDataPath(): string
	{
		return __DIR__ . '/data';
	}

	public function getPhpStanExecutablePath(): string
	{
		return __DIR__ . '/../phpstan.phar';
	}

	public function getPhpStanConfigPath(): ?string
	{
		return __DIR__ . '/phpstan.neon';
	}

}
