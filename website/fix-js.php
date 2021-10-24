<?php declare(strict_types = 1);

$iterator = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
	__DIR__ . '/dist',
)), '/\.js$/');
/** @var \SplFileInfo $file */
foreach ($iterator as $file) {
	if (!is_file($file->getRealPath())) {
		continue;
	}
	$originalEval = 'eval("this")';
	$contents = file_get_contents($file->getRealPath());
	if (strpos($contents, $originalEval) === false) {
		continue;
	}
	$newEval = '(0, eval)("this")';
	$newContents = str_replace($originalEval, $newEval, $contents);
	file_put_contents($file->getRealPath(), $newContents);
}
