<?php

declare(strict_types = 1);

namespace Testing;

use ZipArchive;
use RuntimeException;

$zip = new ZipArchive();

if (!$zip->open('file.zip')) {
	throw new RuntimeException();
}

$stream = $zip->getStream('file.txt');

if ($stream === false) {
	throw new RuntimeException('https://www.php.net/manual/de/ziparchive.getstream.php');
}
