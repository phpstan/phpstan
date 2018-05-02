<?php declare(strict_types = 1);

namespace PHPStan\Parser;

use Nette\DI\Container;
use PhpParser\NodeTraverser;

class NodeTraverserFactory
{

	public const VISITOR_TAG = 'phpstan.nodeTraverser.visitor';

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function create(): NodeTraverser
	{
		$tagToService = function (array $tags) {
			return array_map(function (string $serviceName) {
				return $this->container->getService($serviceName);
			}, array_keys($tags));
		};

		$traverser = new NodeTraverser();
		foreach ($tagToService($this->container->findByTag(self::VISITOR_TAG)) as $visitor) {
			$traverser->addVisitor($visitor);
		}

		return $traverser;
	}

}
