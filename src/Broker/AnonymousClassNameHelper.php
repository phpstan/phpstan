<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Command\ErrorFormatter\RelativePathHelper;

class AnonymousClassNameHelper
{

	/** @var string */
	private $currentWorkingDirectory;

	public function __construct(string $currentWorkingDirectory)
	{
		$this->currentWorkingDirectory = $currentWorkingDirectory;
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
			$this->currentWorkingDirectory,
			$filename
		);

		return sprintf(
			'AnonymousClass%s',
			md5(sprintf('%s:%s', $filename, $node->class->getLine()))
		);
	}

}
