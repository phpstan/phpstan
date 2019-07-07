<?php

namespace InferPrivatePropertyTypeFromConstructor;

class Foo
{

	/** @var int */
	private $intProp;

	private $stringProp;

	private $unionProp;

	private $stdClassProp;

	/**
	 * @param self|Bar $unionProp
	 */
	public function __construct(
		string $intProp,
		string $stringProp,
		$unionProp
	)
	{
		$this->intProp = $intProp;
		$this->stringProp = $stringProp;
		$this->unionProp = $unionProp;
		$this->stdClassProp = new \stdClass();
	}

	public function doFoo()
	{
		die;
	}

}
