<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use PHPStan\File\FileHelper;

abstract class LevelsTestCase extends \PHPUnit\Framework\TestCase
{

	abstract public function dataTopics(): array;

	abstract public function getDataPath(): string;

	abstract public function getPhpStanExecutablePath(): string;

	abstract public function getPhpStanConfigPath(): ?string;

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
			exec(sprintf('php %s analyse --no-progress --error-format=prettyJson --level=%d %s --autoload-file %s %s', $command, $level, $configPath !== null ? '--configuration ' . escapeshellarg($configPath) : '', escapeshellarg($file), escapeshellarg($file)), $outputLines);

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

			$previousMessages = array_merge($previousMessages, $messages);
			$expectedJsonFile = sprintf('%s/%s-%d.json', $this->getDataPath(), $topic, $level);

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
