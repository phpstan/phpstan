<?php declare(strict_types = 1);

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Json;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$tmpResults = [];

$data = [];
foreach ($finder->files()->name('*.json')->in(__DIR__ . '/tmp') as $resultFile) {
	$contents = file_get_contents($resultFile->getPathname());
	if ($contents === false) {
		throw new \LogicException();
	}
	$json = Json::decode($contents, true);
	$repo = $json['repo'];
	$branch = $json['branch'];

	foreach ($json['data'] as $row) {
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
			$dataByIdentifier[$identifier] = [
				'classes' => [
					$row['class'],
				],
				'repos' => [
					$row['repo'],
				],
				'urls' => [
					$row['url'],
				],
			];
			continue;
		}

		$dataByIdentifier[$identifier]['classes'][] = $row['class'];
		$dataByIdentifier[$identifier]['repos'][] = $row['repo'];
		$dataByIdentifier[$identifier]['urls'][] = $row['url'];
	}
}

foreach ($dataByIdentifier as $identifier => $row) {
	foreach ($row as $k => $v) {
		sort($v);
		$dataByIdentifier[$identifier][$k] = array_values(array_unique($v));
	}
}

ksort($dataByIdentifier);

echo Json::encode($dataByIdentifier, true);
