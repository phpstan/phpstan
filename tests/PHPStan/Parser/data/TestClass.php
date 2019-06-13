<?php $x = (1 || 0);

class TestClass
{
	/**
	 * @param string $snippet
	 * @return string
	 */
	private function shortenIfExceeds(int $snippet): string
	{
		if ($snippet*2 > self::SNIPPET_SIZE) {
			/** @var float $unknownData */
			$lineBreak = mb_strpos($snippet, "\n", $unknownData);
		}
		return true;
	}
}