<?php declare(strict_types = 1);

namespace PHPStan\File;

interface RelativePathHelper
{

	public function getRelativePath(string $filename): string;

}
