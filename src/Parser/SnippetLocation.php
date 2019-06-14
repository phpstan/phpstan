<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use PHPStan\Analyser\Error;
use PHPStan\Rules\LineRuleError;
use PHPStan\Rules\RuleError;

class SnippetLocation
{

	private const SNIPPET_SIZE = 255;

	/** @var string */
	private $snippet;

	/** @var string */
	private $fileContents;

	/** @var \PhpParser\Node */
	private $stmt;

	/** @var int */
	private $fileLength;

	/**
	 * @param string $filePath
	 * @param \PhpParser\Node $stmt
	 * @param string|Error|RuleError $ruleError
	 */
	public function __construct(string $filePath, \PhpParser\Node $stmt, $ruleError)
	{
		$this->stmt = $stmt;
		$this->fileContents = $this->loadFile($filePath);

		if ($ruleError instanceof LineRuleError) {
			$currentLine = $ruleError->getLine() - 1;
			$snippet = $this->getSnippetByLineRule($currentLine, $currentLine);
		} elseif ($this->nodeFilePosUnavailable()) {
			$snippet = $this->getSnippetByLineRule($stmt->getAttribute('startLine') - 1, $stmt->getAttribute('endLine') - 1);
		} else {
			$this->fileLength = strlen($this->fileContents);
			$previewStart = $this->getPreviewStartFromNode();
			$previewEnd = $this->getPreviewEndFromNode();
			$snippet = mb_substr($this->fileContents, $previewStart, $previewEnd - $previewStart);
		}

		$snippet = (string) $stmt->getDocComment() . $snippet;

		$this->snippet = $this->shortenIfExceeds($snippet);
	}

	private function getPreviewStartFromNode(): int
	{
		$fileStart = (int) $this->stmt->getAttribute('startFilePos');
		$previewStart = $fileStart;
		$previewStart = (int) mb_strrpos(
			$this->fileContents,
			"\n",
			min($previewStart, $fileStart) - mb_strlen($this->fileContents)
		) + 1;

		return $previewStart;
	}

	private function getPreviewEndFromNode(): int
	{
		$fileLength = $this->fileLength;
		$fileEnd = (int) $this->stmt->getAttribute('endFilePos');
		$selectionEnd = $fileEnd + 1;
		$previewEnd = $selectionEnd;
		if ($selectionEnd <= $fileLength) {
			$previewEnd = @mb_strpos($this->fileContents, "\n", $selectionEnd);
			if ($previewEnd === false) {
				$previewEnd = $selectionEnd;
			}
		}

		return $previewEnd;
	}

	public function getSnippet(): string
	{
		return $this->snippet;
	}

	private function shortenIfExceeds(string $snippet): string
	{
		if (mb_strlen($snippet) > self::SNIPPET_SIZE) {
			$lineBreak = @mb_strpos($snippet, "\n", self::SNIPPET_SIZE);
			if ($lineBreak === false) {
				$lineBreak = strlen($snippet);
			}
			$snippet = mb_substr($snippet, 0, $lineBreak);
		}
		return $snippet;
	}

	private function getSnippetByLineRule(int $lineNumberFrom, int $lineNumberTo): string
	{
		$lines = explode("\n", $this->fileContents);
		return implode("\n", array_slice($lines, $lineNumberFrom, ($lineNumberTo - $lineNumberFrom) + 1));
	}

	private function nodeFilePosUnavailable(): bool
	{
		return $this->stmt->getAttribute('startFilePos') === null;
	}

	private function loadFile(string $filePath): string
	{
		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		return $data;
	}

}
