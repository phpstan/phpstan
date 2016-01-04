<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class Scope
{

	/**
	 * @var string|null
	 */
	private $class;

	/**
	 * @var string|null
	 */
	private $function;

	/**
	 * @var string|null
	 */
	private $namespace;

	/**
	 * @var bool
	 */
	private $inClosureBind;

	public function __construct(
		string $class = null,
		string $function = null,
		string $namespace = null,
		bool $inClosureBind
	)
	{
		if ($class === '') {
			$class = null;
		}
		if ($function === '') {
			$function = null;
		}
		if ($namespace === '') {
			$namespace = null;
		}
		$this->class = $class;
		$this->function = $function;
		$this->namespace = $namespace;
		$this->inClosureBind = $inClosureBind;
	}

	/**
	 * @return null|string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @return null|string
	 */
	public function getFunction()
	{
		return $this->function;
	}

	/**
	 * @return null|string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	public function isInClosureBind(): bool
	{
		return $this->inClosureBind;
	}

}
