<?php

namespace DynamicMethodReturnTypesNamespace;

class EntityManager
{

	public function getByPrimary(string $className, int $id): Entity
	{
		return new $className();
	}

	public static function createManagerForEntity(string $className): self
	{

	}

}

class InheritedEntityManager extends EntityManager
{

}

class ComponentContainer implements \ArrayAccess
{

	public function offsetExists($offset)
	{

	}

	public function offsetGet($offset): Entity
	{

	}

	public function offsetSet($offset, $value)
	{

	}

	public function offsetUnset($offset)
	{

	}

}

class Foo
{

	public function __construct()
	{
	}

	public function doFoo()
	{
		$em = new EntityManager();
		$iem = new InheritedEntityManager();
		$container = new ComponentContainer();
		die;
	}

}

class FooWithoutConstructor
{

}
