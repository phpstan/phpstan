<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Cache\Cache;
use PHPStan\Rules\Rule;

abstract class RuleTestCase extends TestCase
{

	abstract protected function getRule(): Rule;

	public function analyse(array $files, array $expectedErrors): void
	{
		$expectedErrors = $this->formatExpectedErrors($expectedErrors);

		$files = array_map([$this->testingKit->getFileHelper(), 'normalizePath'], $files);

		/** @var Cache $cache */
		$cache = $this->createMock(Cache::class);

		/** @var Analyser $analyser */
		$analyser = $this->testingKit->getAnalyser(
			$this->getRule(),
			$cache,
			$this->shouldPolluteScopeWithLoopInitialAssignments(),
			$this->shouldPolluteCatchScopeWithTryAssignments()
		);

		$actualErrors = $analyser->analyse($files, false);

		$this->assertInternalType('array', $actualErrors);

		$actualErrors = array_map(function (Error $error): string {
			return $this->formatError($error->getLine(), $error->getMessage());
		}, $actualErrors);

		$this->assertSame(
			implode("\n", $expectedErrors),
			implode("\n", $actualErrors)
		);
	}

	private function formatExpectedErrors(array $expectedErrors): array
	{
		return array_map(function (array $error): string {
			if (!isset($error[0])) {
				throw new \InvalidArgumentException('Missing expected error message.');
			}
			if (!isset($error[1])) {
				throw new \InvalidArgumentException('Missing expected file line.');
			}

			return $this->formatError($error[1], $error[0]);
		}, $expectedErrors);
	}

	private function formatError(?int $line, string $message): string
	{
		return sprintf('%02d: %s', $line, $message);
	}

	protected function shouldPolluteScopeWithLoopInitialAssignments(): bool
	{
		return false;
	}

	protected function shouldPolluteCatchScopeWithTryAssignments(): bool
	{
		return false;
	}

}
