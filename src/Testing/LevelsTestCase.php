<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\File\FileHelper;

abstract class LevelsTestCase extends \PHPUnit\Framework\TestCase
{

	abstract public function dataTopics(): array;

	abstract public function getDataPath(): string;

	abstract public function getPhpStanExecutablePath(): string;

	abstract public function getPhpStanConfigPath(): ?string;

	protected function getResultSuffix(): string
	{
		return '';
	}

	/**
	 * @dataProvider dataTopics
	 * @param string $topic
	 */
	public function testLevels(
		string $topic
	): void
	{
		$file = sprintf('%s/%s.php', $this->getDataPath(), $topic);
		$command = escapeshellcmd($this->getPhpStanExecutablePath());
		$configPath = $this->getPhpStanConfigPath();
		$fileHelper = new FileHelper(__DIR__ . '/../..');

		$previousMessages = [];

		$exceptions = [];

		foreach (range(0, 7) as $level) {
			unset($outputLines);
			exec(sprintf('%s %s analyse --no-progress --error-format=prettyJson --level=%d %s --autoload-file %s %s', escapeshellarg(PHP_BINARY), $command, $level, $configPath !== null ? '--configuration ' . escapeshellarg($configPath) : '', escapeshellarg($file), escapeshellarg($file)), $outputLines);

			$output = implode("\n", $outputLines);

			try {
				$actualJson = \Nette\Utils\Json::decode($output, \Nette\Utils\Json::FORCE_ARRAY);
			} catch (\Nette\Utils\JsonException $e) {
				throw new \Nette\Utils\JsonException(sprintf('Cannot decode: %s', $output));
			}
			if (count($actualJson['files']) > 0) {
				$messagesBeforeDiffing = $actualJson['files'][$fileHelper->normalizePath($file)]['messages'];
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

			$missingMessages = [];
			foreach ($previousMessages as $previousMessage) {
				foreach ($messagesBeforeDiffing as $message) {
					if (
						$previousMessage['message'] === $message['message']
						&& $previousMessage['line'] === $message['line']
					) {
						continue 2;
					}
				}

				$missingMessages[] = $previousMessage;
			}

			$previousMessages = array_merge($previousMessages, $messages);
			$expectedJsonFile = sprintf('%s/%s-%d%s.json', $this->getDataPath(), $topic, $level, $this->getResultSuffix());

			$exception = $this->compareFiles($expectedJsonFile, $messages);
			if ($exception !== null) {
				$exceptions[] = $exception;
			}

			$expectedJsonMissingFile = sprintf('%s/%s-%d-missing%s.json', $this->getDataPath(), $topic, $level, $this->getResultSuffix());
			$exception = $this->compareFiles($expectedJsonMissingFile, $missingMessages);
			if ($exception === null) {
				continue;
			}

			$exceptions[] = $exception;
		}

		if (count($exceptions) > 0) {
			throw $exceptions[0];
		}
	}

	private function compareFiles(string $expectedJsonFile, array $expectedMessages): ?\PHPUnit\Framework\AssertionFailedError
	{
		if (count($expectedMessages) === 0) {
			try {
				$this->assertFileNotExists($expectedJsonFile);
				return null;
			} catch (\PHPUnit\Framework\AssertionFailedError $e) {
				unlink($expectedJsonFile);
				return $e;
			}
		}

		$actualOutput = \Nette\Utils\Json::encode($expectedMessages, \Nette\Utils\Json::PRETTY);

		try {
			$this->assertJsonStringEqualsJsonFile(
				$expectedJsonFile,
				$actualOutput
			);
		} catch (\PHPUnit\Framework\AssertionFailedError $e) {
			file_put_contents($expectedJsonFile, $actualOutput);
			return $e;
		}

		return null;
	}

}
