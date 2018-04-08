<?php

namespace TestInstantiation;

class InstantiatingClass
{

	public function __construct(int $i)
	{

	}

	public function doFoo()
	{
		new self();
		new self(1);
		new static(); // not checked
		new parent();
	}

}

function () {
	new FooInstantiation;
	new FooInstantiation();
	new FooInstantiation(1); // additional parameter
	new FooBarInstantiation(); // nonexistent
	new BarInstantiation(); // missing parameter
	new LoremInstantiation(); // abstract
	new IpsumInstantiation(); // interface

	$test = 'Test';
	new $test(); // skip

	new ClassWithVariadicConstructor(1, 2, 3);
	new \DatePeriod();
	new \DatePeriod(new \DateTime(), new \DateInterval('P1D'), new \DateTime(), \DatePeriod::EXCLUDE_START_DATE);

	new self();
	new static();
	new parent();
};

class ChildInstantiatingClass extends InstantiatingClass
{

	public function __construct(int $i, int $j)
	{
		parent::__construct($i);
	}

	public function doBar()
	{
		new parent();
		new parent(1);
	}

}

function () {
	new FOOInstantiation(1, 2, 3);
	new BARInstantiation();
	new BARInstantiation(1);
};

class PrivateConstructorClass
{

	private function __construct(int $i)
	{

	}

}

class ProtectedConstructorClass
{

	protected function __construct(int $i)
	{

	}

}

class ClassExtendsProtectedConstructorClass extends ProtectedConstructorClass
{

	public function doFoo()
	{
		new self();
	}

}

class ExtendsPrivateConstructorClass extends PrivateConstructorClass
{

	public function doFoo()
	{
		new self();
	}

}

function () {
	new PrivateConstructorClass(1);
	new ProtectedConstructorClass(1);
	new ClassExtendsProtectedConstructorClass(1);
	new ExtendsPrivateConstructorClass(1);
};

function () {
	new \Exception(123, 'code');
};

class NoConstructor
{

}

function () {
	new NoConstructor();
	new NOCONSTRUCTOR();
};
