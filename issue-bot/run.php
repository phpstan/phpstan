<?php declare(strict_types = 1);

namespace App;

use App\BotCommentParser\BotCommentParser;
use App\BotCommentParser\BotCommentParserException;
use App\Playground\PlaygroundClient;
use App\Playground\PlaygroundExample;
use AppendIterator;
use DateTimeImmutable;
use Github\Client;
use Github\HttpClient\Builder;
use GuzzleHttp\Promise\Utils;
use Iterator;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

require_once __DIR__ . '/vendor/autoload.php';

$token = $_SERVER['GITHUB_PAT'];
$phpstanSrcCommitBefore = $_SERVER['PHPSTAN_SRC_COMMIT_BEFORE'];
$phpstanSrcCommitAfter = $_SERVER['PHPSTAN_SRC_COMMIT_AFTER'];

$rateLimitPlugin = new RateLimitPlugin();

$gitHubRequestCounter = new RequestCounterPlugin();

$httpBuilder = new Builder();
$httpBuilder->addPlugin($rateLimitPlugin);
$httpBuilder->addPlugin($gitHubRequestCounter);

$client = new Client($httpBuilder);
$client->authenticate($token, Client::AUTH_ACCESS_TOKEN);

$rateLimitPlugin->setClient($client);

$playgroundClient = new PlaygroundClient(new \GuzzleHttp\Client());

$markdownEnvironment = Environment::createCommonMarkEnvironment();
$markdownEnvironment->addExtension(new GithubFlavoredMarkdownExtension());
$botCommentParser = new BotCommentParser(new DocParser($markdownEnvironment));

/**
 * @param string $label
 * @return Iterator<int, Issue>
 */
function getIssues(string $label): Iterator
{
	/** @var Client */
	global $client;

	$page = 1;

	/** @var \Github\Api\Issue $api */
	$api = $client->api('issue');
	while (true) {
		$newIssues = $api->all('phpstan', 'phpstan', [
			'state' => 'open',
			'labels' => $label,
			'page' => $page,
			'per_page' => 100,
			'sort' => 'created',
			'direction' => 'desc',
		]);

		yield from array_map(function (array $issue): Issue {
			return new Issue(
				$issue['number'],
				$issue['user']['login'],
				$issue['body'],
				DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $issue['updated_at']), // @phpstan-ignore-line
				getComments($issue['number']),
				searchBody($issue['body'], $issue['user']['login'])
			);
		}, $newIssues);

		if (count($newIssues) < 100) {
			break;
		}

		$page++;
	}
}

/**
 * @param int $issueNumber
 * @return Comment[]
 */
function getComments(int $issueNumber): iterable
{
	if ($issueNumber === 7454 || $issueNumber === 3610) {
		return [];
	}

	/** @var Client */
	global $client;
	$page = 1;

	/** @var BotCommentParser */
	global $botCommentParser;

	/** @var \Github\Api\Issue $api */
	$api = $client->api('issue');
	while (true) {
		try {
			$newComments = $api->comments()->all('phpstan', 'phpstan', $issueNumber, [
				'page' => $page,
				'per_page' => 100,
			]);
		} catch (\Github\Exception\RuntimeException $e) {
			var_dump($e->getMessage());
			var_dump($e->getCode());
			var_dump($issueNumber, $page);
			throw $e;
		}
		if (count($newComments) === 0) {
			break;
		}

		yield from array_map(function (array $comment) use ($botCommentParser): Comment {
			$examples = searchBody($comment['body'], $comment['user']['login']);
			if ($comment['user']['login'] === 'phpstan-bot') {
				$parserResult = $botCommentParser->parse($comment['body']);
				if (count($examples) !== 1 || $examples[0]->getHash() !== $parserResult->getHash()) {
					throw new BotCommentParserException();
				}

				return new BotComment($comment['body'], $examples[0], $parserResult->getDiff());
			}

			return new Comment($comment['user']['login'], $comment['body'], $examples);
		}, $newComments);
		$page++;
	}
}

/**
 * @param string $text
 * @param string $author
 * @return PlaygroundExample[]
 */
function searchBody(string $text, string $author): array
{
	/** @var PlaygroundClient */
	global $playgroundClient;
	$matches = \Nette\Utils\Strings::matchAll($text, '/https:\/\/phpstan\.org\/r\/([0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12})/i');

	$examples = [];

	foreach ($matches as [$url, $hash]) {
		$examples[] = new PlaygroundExample($url, $hash, $author, $playgroundClient->getResultPromise($hash, $author));
	}

	return $examples;
}

exec('git branch --show-current', $gitBranchLines, $exitCode);
if ($exitCode === 0) {
	$gitBranch = implode("\n", $gitBranchLines);
} else {
	$gitBranch = 'dev-master';
}

$postGenerator = new PostGenerator(new Differ(new UnifiedDiffOutputBuilder('')), $gitBranch, $phpstanSrcCommitBefore, $phpstanSrcCommitAfter);
$promiseResolver = new PromiseResolver();

$issuesIterator = new AppendIterator();
$issuesIterator->append(getIssues('bug'));
$issuesIterator->append(getIssues('feature-request'));

foreach ($issuesIterator as $issue) {
	$deduplicatedExamples = [];

	foreach ($issue->getPlaygroundExamples() as $example) {
		$deduplicatedExamples[$example->getHash()] = $example;
	}

	$botComments = [];
	foreach ($issue->getComments() as $comment) {
		if ($comment instanceof BotComment) {
			$botComments[] = $comment;
			continue;
		}
		foreach ($comment->getPlaygroundExamples() as $example2) {
			if (isset($deduplicatedExamples[$example2->getHash()])) {
				$deduplicatedExamples[$example2->getHash()]->addUser($comment->getAuthor());
				continue;
			}
			$deduplicatedExamples[$example2->getHash()] = $example2;
		}
	}

	$issueResultsPromises = [];
	foreach ($deduplicatedExamples as $example) {
		$issueResultsPromises[] = $example->getResultPromise();
	}

	$promise = Utils::all($issueResultsPromises)->then(function (array $results) use ($postGenerator, $client, $botComments, $issue): void {
		foreach ($results as $result) {
			$text = $postGenerator->createText($result, $botComments);
			if ($text === null) {
				continue;
			}

			/** @var \Github\Api\Issue $issueApi */
			$issueApi = $client->api('issue');
			echo sprintf("Posting comment to issue https://github.com/phpstan/phpstan/issues/%d\n", $issue->getNumber());

			$issueApi->comments()->create('phpstan', 'phpstan', $issue->getNumber(), [
				'body' => $text,
			]);
		}
	}, function (\Throwable $e) {
		echo sprintf("%s: %s\n", get_class($e), $e->getMessage());
	});

	$promiseResolver->push($promise, count($issueResultsPromises));
}

$promiseResolver->flush();

echo sprintf("Total playground requests: %d\n", $promiseResolver->getTotalCount());
echo sprintf("Total GitHub requests: %d\n", $gitHubRequestCounter->getTotalCount());
