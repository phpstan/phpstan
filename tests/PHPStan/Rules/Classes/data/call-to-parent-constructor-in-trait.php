<?php

namespace CallToParentConstructorInTrait;

trait AcmeTrait
{
	public function __construct()
	{
	}
}

class BaseAcme
{
	public function __construct()
	{
	}
}

class Acme extends BaseAcme
{
	use AcmeTrait {
		AcmeTrait::__construct as private __acmeConstruct;
	}

	public function __construct()
	{
		$this->__acmeConstruct();

		parent::__construct();
	}
}
