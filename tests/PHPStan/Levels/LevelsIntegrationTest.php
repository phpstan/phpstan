<?php declare(strict_types = 1);

namespace PHPStan\Levels;

class LevelsIntegrationTest extends \PHPStan\Testing\LevelsTestCase
{

    public function dataTopics(): array
    {
        return [
            ['returnTypes'],
            ['acceptTypes'],
            ['methodCalls'],
            ['propertyAccesses'],
            ['constantAccesses'],
            ['variables'],
            ['callableCalls'],
            ['arrayDimFetches'],
            ['clone'],
            ['iterable'],
            ['binaryOps'],
            ['throwValues'],
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
        return null;
    }
}
