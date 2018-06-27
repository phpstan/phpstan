<?php

namespace CallMethodsIterables;

class Uuid
{

    /**
     * @param Uuid[] $ids
     */
    public function bar(iterable $ids)
    {
        $id = new self();
        $id->bar([null]);
    }
}

class Foo
{

    /**
     * @return self[]|iterable
     */
    public function getIterable(): iterable
    {
    }

    /**
     * @return Bar[]|iterable
     */
    public function getOtherIterable(): iterable
    {
    }

    /**
     * @param array|\Traversable $iterable
     */
    public function acceptsArrayOrTraversable($iterable)
    {
    }

    /**
     * @param self[]|iterable $iterable
     */
    public function acceptsSelfIterable(iterable $iterable)
    {
    }

    public function test()
    {
        $this->acceptsArrayOrTraversable($this->getIterable());
        $this->acceptsSelfIterable($this->getIterable());
        $this->acceptsArrayOrTraversable($this->getOtherIterable());
        $this->acceptsSelfIterable($this->getOtherIterable());
        $this->acceptsSelfIterable('foo');

        $this->doFoo(1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
    }

    /**
     * @param iterable $iterableWithIterableTypehint
     * @param Bar[] $iterableWithConcreteTypehint
     * @param iterable $arrayWithIterableTypehint
     * @param Bar[]|Collection $unionIterableType
     * @param Foo[]|Bar[]|Collection|array $mixedUnionIterableType
     * @param Bar[]|Collection $unionIterableIterableType
     * @param int[]|iterable $integers
     * @param mixed[]|iterable $mixeds
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
        iterable $mixeds
    ) {
    }
}

class Bar
{

}

interface Collection extends \Traversable
{

}
