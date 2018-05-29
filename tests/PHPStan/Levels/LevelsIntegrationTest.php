<?php declare(strict_types = 1);

namespace PHPStan\Levels;

class LevelsIntegrationTest extends \PHPUnit\Framework\TestCase
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
		];
	}

	/**
	 * @dataProvider dataTopics
	 * @param string $topic
	 */
	public function testLevels(
		string $topic
	): void
	{
		$file = sprintf('%s/data/%s.php', __DIR__, $topic);
		$command = escapeshellcmd(__DIR__ . '/../../../bin/phpstan');

		$previousMessages = [];

		$exceptions = [];

		foreach (range(0, 7) as $level) {
			unset($outputLines);
			exec(sprintf('%s analyse --no-progress --errorFormat=prettyJson --level=%d --autoload-file %s %s', $command, $level, escapeshellarg($file), escapeshellarg($file)), $outputLines);

			$actualJson = \Nette\Utils\Json::decode(implode("\n", $outputLines), \Nette\Utils\Json::FORCE_ARRAY);
			if (count($actualJson['files']) > 0) {
				$messagesBeforeDiffing = $actualJson['files'][pathinfo($file, PATHINFO_BASENAME)]['messages'];
			} else {
				$messagesBeforeDiffing = [];
			}

			$messages = [];
			foreach ($messagesBeforeDiffing as $message) {
				foreach ($previousMessages as $lastMessage) {
					if (
						$message['message'] === $lastMessage['message']
						&& $message['line'] === $lastMessage['line']
					) {
						continue 2;
					}
				}

				$messages[] = $message;
			}

			$previousMessages = array_merge($previousMessages, $messages);
			$expectedJsonFile = sprintf('%s/data/%s-%d.json', __DIR__, $topic, $level);

			if (count($messages) === 0) {
				try {
					$this->assertFileNotExists($expectedJsonFile);
					continue;
				} catch (\PHPUnit\Framework\AssertionFailedError $e) {
					unlink($expectedJsonFile);
					$exceptions[] = $e;
					continue;
				}
			}

			$actualOutput = \Nette\Utils\Json::encode($messages, \Nette\Utils\Json::PRETTY);

			try {
				$this->assertJsonStringEqualsJsonFile(
					$expectedJsonFile,
					$actualOutput,
					sprintf('Level #%d - file %s', $level, pathinfo($file, PATHINFO_BASENAME))
				);
			} catch (\PHPUnit\Framework\AssertionFailedError $e) {
				file_put_contents($expectedJsonFile, $actualOutput);
				$exceptions[] = $e;
			}
		}

		if (count($exceptions) > 0) {
			throw $exceptions[0];
		}
	}

}
