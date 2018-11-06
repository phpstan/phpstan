<?php

namespace Iterables;

interface Collection extends \Traversable
{

}

interface CollectionOfIntegers extends \Iterator
{
	public function current(): int;
}

class Foo
{

	/**
	 * @var iterable
	 */
	private $iterableProperty;

	/**
	 * @var string[]|iterable
	 */
	private $stringIterableProperty;

	/**
	 * @var mixed[]|iterable
	 */
	private $mixedIterableProperty;

	/**
	 * @var string[]|iterable|int
	 */
	private $iterablePropertyAlsoWithSomethingElse;

	/**
	 * @var string[]|int[]|iterable|int
	 */
	private $iterablePropertyWithTwoItemTypes;

	/**
	 * @var CollectionOfIntegers|string[]
	 */
	private $collectionOfIntegersOrArrayOfStrings;

	/**
	 * @param iterable $iterableWithIterableTypehint
	 * @param Bar[] $iterableWithConcreteTypehint
	 * @param iterable $arrayWithIterableTypehint
	 * @param Bar[]|Collection $unionIterableType
	 * @param Foo[]|Bar[]|Collection|array $mixedUnionIterableType
	 * @param Bar[]|Collection $unionIterableIterableType
	 * @param int[]|iterable $integers
	 * @param mixed[]|iterable $mixeds
	 * @param \Generator<Foo> $generatorOfFoos
	 * @param \ArrayObject<int, string> $arrayObject
	 */
	public function doFoo(
		iterable $iterableWithoutTypehint,
		iterable $iterableWithIterableTypehint,
		iterable $iterableWithConcreteTypehint,
		array $arrayWithIterableTypehint,
		Collection $unionIterableType,
		array $mixedUnionIterableType,
		iterable $unionIterableIterableType,
		$iterableSpecifiedLater,
		iterable $integers,
		iterable $mixeds,
		$generatorOfFoos,
		$arrayObject
	)
	{
		if (!is_iterable($iterableSpecifiedLater)) {
			return;
		}

		foreach ($iterableWithIterableTypehint as $mixed) {
			foreach ($iterableWithConcreteTypehint as $bar) {
				foreach ($this->doBaz() as $baz) {
					foreach ($unionIterableType as $unionBar) {
						foreach ($mixedUnionIterableType as $mixedBar) {
							foreach ($unionIterableIterableType as $iterableUnionBar) {
								foreach ($this->doUnionIterableWithPhpDoc() as $unionBarFromMethod) {
									foreach ($generatorOfFoos as $fooFromGenerator) {
										foreach ($arrayObject as $arrayObjectKey => $arrayObjectValue) {
											die;
										}
									}
								}
							}
						}
					}
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

	/**
	 * @return Bar[]|\Traversable
	 */
	public function doUnionIterableWithPhpDoc(): \Traversable
	{

	}

	/**
	 * @return iterable|mixed[]
	 */
	public function returnIterableMixed(): iterable
	{

	}

	/**
	 * @return iterable|string[]
	 */
	public function returnIterableString(): iterable
	{

	}

}
