<?php

namespace MethodSignature;

class Animal
{
}

class Dog extends Animal
{
}

class Cat extends Animal
{
}

class BaseClass
{

	/**
	 * @param Animal $animal
	 */
	public function __construct($animal)
	{
	}

	public function parameterTypeTest1()
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest2($animal)
	{
	}

	/**
	 * @param Dog $animal
	 */
	public function parameterTypeTest3($animal)
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest4($animal)
	{
	}

	/**
	 * @param Cat $animal
	 */
	public function parameterTypeTest5($animal)
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest6($animal)
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest7($animal)
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest8($animal)
	{
	}

	/**
	 * @return void
	 */
	public function returnTypeTest1()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest2()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest3()
	{
	}

	/**
	 * @return Dog
	 */
	public function returnTypeTest4()
	{
	}

	/**
	 * @return Dog
	 */
	public function returnTypeTest5()
	{
	}

	/**
	 * @return Animal|null
	 */
	public function returnTypeTest6()
	{
	}

	/**
	 * @return mixed
	 */
	public function returnTypeTest7()
	{
	}

	/**
	 * @return mixed
	 */
	public function returnTypeTest8()
	{
	}

	/**
	 * @return mixed
	 */
	public function returnTypeTest9()
	{
	}

}

interface BaseInterface
{

	/**
	 * @param Animal $animal
	 */
	public function __construct($animal);

	public function parameterTypeTest1();

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest2($animal);

	/**
	 * @param Dog $animal
	 */
	public function parameterTypeTest3($animal);

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest4($animal);

	/**
	 * @param Cat $animal
	 */
	public function parameterTypeTest5($animal);

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest6($animal);

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest7($animal);

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest8($animal);

	/**
	 * @return void
	 */
	public function returnTypeTest1();

	/**
	 * @return Animal
	 */
	public function returnTypeTest2();

	/**
	 * @return Animal
	 */
	public function returnTypeTest3();

	/**
	 * @return Dog
	 */
	public function returnTypeTest4();

	/**
	 * @return Dog
	 */
	public function returnTypeTest5();

	/**
	 * @return Animal|null
	 */
	public function returnTypeTest6();

	public function returnTypeTest7();

	/**
	 * @return mixed
	 */
	public function returnTypeTest8();

	public function returnTypeTest9();

}

trait SubTrait
{

	/**
	 * @param Dog $animal
	 */
	public function __construct($animal)
	{
	}

	public function parameterTypeTest1()
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest2($animal)
	{
	}

	/**
	 * @param Animal $animal
	 */
	public function parameterTypeTest3($animal)
	{
	}

	/**
	 * @param Dog $animal
	 */
	public function parameterTypeTest4($animal)
	{
	}

	/**
	 * @param Dog $animal
	 */
	public function parameterTypeTest5($animal)
	{
	}

	/**
	 * @param Animal|null $animal
	 */
	public function parameterTypeTest6($animal)
	{
	}

	public function parameterTypeTest7($animal)
	{
	}

	/**
	 * @param mixed $animal
	 */
	public function parameterTypeTest8($animal)
	{
	}

	/**
	 * @return mixed
	 */
	public function returnTypeTest1()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest2()
	{
	}

	/**
	 * @return Dog
	 */
	public function returnTypeTest3()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest4()
	{
	}

	/**
	 * @return Cat
	 */
	public function returnTypeTest5()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest6()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest7()
	{
	}

	/**
	 * @return Animal
	 */
	public function returnTypeTest8()
	{
	}

	/**
	 * @return void
	 */
	public function returnTypeTest9()
	{
	}

}
