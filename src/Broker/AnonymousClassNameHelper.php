<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Command\ErrorFormatter\RelativePathHelper;
use PHPStan\File\FileHelper;

class AnonymousClassNameHelper
{

	/** @var FileHelper */
	private $fileHelper;

	public function __construct(
		FileHelper $fileHelper
	)
	{
		$this->fileHelper = $fileHelper;
	}

	public function getAnonymousClassName(
		\PhpParser\Node\Expr\New_ $node,
		string $filename
	): string
	{
		if (!$node->class instanceof \PhpParser\Node\Stmt\Class_) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$filename = RelativePathHelper::getRelativePath(
			$this->fileHelper->getWorkingDirectory(),
			$this->fileHelper->normalizePath($filename)
		);

		return sprintf(
			'AnonymousClass%s',
			md5(sprintf('%s:%s', $filename, $node->class->getLine()))
		);
	}

}
