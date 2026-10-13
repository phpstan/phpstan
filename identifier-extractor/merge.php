<?php declare(strict_types = 1);

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Json;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$tmpResults = [];

$data = [];
$classes = [];
foreach ($finder->files()->name('*.json')->in(__DIR__ . '/tmp') as $resultFile) {
	$contents = file_get_contents($resultFile->getPathname());
	if ($contents === false) {
		throw new \LogicException(sprintf('Could not read %s', $resultFile->getPathname()));
	}
	$json = Json::decode($contents, true);
	if (!is_array($json) || !is_string($json['repo'] ?? null) || !is_string($json['branch'] ?? null) || !is_array($json['data'] ?? null)) {
		throw new \LogicException(sprintf('Malformed identifier artifact: %s', $resultFile->getPathname()));
	}
	$repo = $json['repo'];
	$branch = $json['branch'];

	foreach ($json['data'] as $row) {
		if (!is_array($row) || !is_array($row['identifiers'] ?? null) || !isset($row['class'], $row['file'], $row['line'])) {
			throw new \LogicException(sprintf('Malformed row in %s', $resultFile->getPathname()));
		}
		$classes[$row['class']] = true;
		$data[] = [
			'identifiers' => $row['identifiers'],
			'class' => $row['class'],
			'repo' => $repo,
			'url' => sprintf('https://github.com/%s/blob/%s/%s#L%d', $repo, $branch, $row['file'], $row['line']),
		];
	}
}

$dataByIdentifier = [];
foreach ($data as $row) {
	foreach ($row['identifiers'] as $identifier) {
		if (!isset($dataByIdentifier[$identifier])) {
			$dataByIdentifier[$identifier] = [];
		}
		$class = $row['class'];
		if (!isset($dataByIdentifier[$identifier][$class])) {
			$dataByIdentifier[$identifier][$class] = [];
		}

		$repo = $row['repo'];
		if (!isset($dataByIdentifier[$identifier][$class][$repo])) {
			$dataByIdentifier[$identifier][$class][$repo] = [];
		}

		$dataByIdentifier[$identifier][$class][$repo][] = $row['url'];
	}
}

ksort($dataByIdentifier);

foreach ($dataByIdentifier as $identifier => $rows) {
	ksort($rows);
	$dataByIdentifier[$identifier] = $rows;
	foreach ($rows as $class => $repos) {
		foreach ($repos as $repo => $urls) {
			$urls = array_values(array_unique($urls));
			sort($urls);
			$repos[$repo] = $urls;
		}

		ksort($repos);
		$dataByIdentifier[$identifier][$class] = $repos;
	}
}

$identifierCount = count($dataByIdentifier);
$classesCount = count($classes);

fwrite(STDERR, sprintf("Total: %d identifiers in %d rules\n", $identifierCount, $classesCount));

echo Json::encode($dataByIdentifier, true);
