<?php declare(strict_types = 1);

namespace App;

use App\Playground\PlaygroundExample;
use App\Playground\PlaygroundResult;
use App\Playground\PlaygroundResultError;
use App\Playground\PlaygroundResultTab;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

class PostGeneratorTest extends TestCase
{

	/**
	 * @return iterable<array{PlaygroundResult, BotComment[], string|null}>
	 */
	public function dataGeneratePosts(): iterable
	{
		$diff = '@@ @@
-1: abc
+1: def
';

		$commentText = "@foobar After [the latest commit in dev-master](https://github.com/phpstan/phpstan-src/commit/abc123), PHPStan now reports different result with your [code snippet](https://phpstan.org/r/abc-def):

```diff\n" . $diff . '```

<details>
 <summary>Full report</summary>

| Line | Error |
|---|---|
| 1 | `def` |

</details>';

		yield [
			new PlaygroundResult('abc-def', ['foobar'], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('abc', 1),
				]),
			], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('abc', 1),
				]),
			]),
			[],
			null,
		];

		yield [
			new PlaygroundResult('abc-def', ['foobar'], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('abc', 1),
				]),
			], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('def', 1),
				]),
			]),
			[],
			$commentText,
		];

		yield [
			new PlaygroundResult('abc-def', ['foobar'], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('abc', 1),
				]),
			], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('def', 1),
				]),
			]),
			[
				new BotComment('<text>', new PlaygroundExample('', 'abc-def', 'ondrejmirtes', new FulfilledPromise('foo')), 'some diff'),
			],
			$commentText,
		];

		yield [
			new PlaygroundResult('abc-def', ['foobar'], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('abc', 1),
				]),
			], [
				new PlaygroundResultTab('PHP 7.1', [
					new PlaygroundResultError('def', 1),
				]),
			]),
			[
				new BotComment('<text>', new PlaygroundExample('', 'abc-def', 'ondrejmirtes', new FulfilledPromise('foo')), $diff),
			],
			null,
		];
	}

	/**
	 * @dataProvider dataGeneratePosts
	 * @param BotComment[] $botComments
	 */
	public function testGeneratePosts(
		PlaygroundResult $result,
		array $botComments,
		?string $expectedText
	): void
	{
		$generator = new PostGenerator(new Differ(new UnifiedDiffOutputBuilder('')), 'abc123');
		$text = $generator->createText(
			$result,
			$botComments
		);
		$this->assertSame($expectedText, $text);
	}

}
