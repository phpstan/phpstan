<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

class ErrorFormatterFactory {

	/** @var string */
	private $formatType;

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(string $formatType, \Nette\DI\Container $container) {
		$this->formatType = $formatType;
		$this->container = $container;
	}

	public function create() {
		return $this->container->getService('errorFormatter.'.$this->formatType);
	}
}