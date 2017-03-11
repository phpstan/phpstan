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
     * @param Bar[]|Collection $unionIterableType
     * @param Foo[]|Bar[]|Collection $mixedUnionIterableType
     * @param Bar[]|Collection $unionIterableIterableType
     */
    public function doFoo(
        iterable $iterableWithoutTypehint,
        iterable $iterableWithIterableTypehint,
        iterable $iterableWithConcreteTypehint,
        array $arrayWithIterableTypehint,
        Collection $unionIterableType,
        array $mixedUnionIterableType,
        iterable $unionIterableIterableType,
        $iterableSpecifiedLater
    ) {
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
                                    die;
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
}
