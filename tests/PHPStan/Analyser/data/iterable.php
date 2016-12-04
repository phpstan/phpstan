<?php // lint >= 7.1

namespace Iterables;

class Foo
{

	/**
	 * @var iterable
	 */
	private $iterableProperty;

	/**
	 * @param iterable $iterableWithIterableTypehint
	 * @param Bar[] $iterableWithConcreteTypehint
	 * @param iterable $arrayWithIterableTypehint
	 */
	public function doFoo(
		iterable $iterableWithoutTypehint,
		iterable $iterableWithIterableTypehint,
		iterable $iterableWithConcreteTypehint,
		array $arrayWithIterableTypehint,
		$iterableSpecifiedLater
	)
	{
		if (!is_iterable($iterableSpecifiedLater)) {
			return;
		}

		foreach ($iterableWithIterableTypehint as $mixed) {
			foreach ($iterableWithConcreteTypehint as $bar) {
				foreach ($this->doBaz() as $baz) {
					die;
				}
			}
		}
	}

	/**
	 * @return iterable
	 */
	public function doBar(): iterable
	{

	}

	/**
	 * @return Baz[]
	 */
	public function doBaz(): iterable
	{

	}

}
