<?php declare(strict_types = 1);

namespace App\BotCommentParser;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\DocParser;
use League\CommonMark\Inline\Element\Link;

class BotCommentParser
{

	private DocParser $docParser;

	public function __construct(DocParser $docParser)
	{
		$this->docParser = $docParser;
	}

	public function parse(string $text): BotCommentParserResult
	{
		$document = $this->docParser->parse($text);
		$walker = $document->walker();
		$hashes = [];
		$diffs = [];
		while ($event = $walker->next()) {
			if (!$event->isEntering()) {
				continue;
			}

			$node = $event->getNode();
			if ($node instanceof Link) {
				$url = $node->getUrl();
				$match = \Nette\Utils\Strings::match($url, '/^https:\/\/phpstan\.org\/r\/([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})$/i');
				if ($match === null) {
					continue;
				}

				$hashes[] = $match[1];
				continue;
			}

			if (!($node instanceof FencedCode)) {
				continue;
			}

			if ($node->getInfo() !== 'diff') {
				continue;
			}

			$diffs[] = $node->getStringContent();
		}

		if (count($hashes) !== 1) {
			throw new \App\BotCommentParser\BotCommentParserException();
		}

		if (count($diffs) !== 1) {
			throw new \App\BotCommentParser\BotCommentParserException();
		}

		return new BotCommentParserResult($hashes[0], $diffs[0]);
	}

}
