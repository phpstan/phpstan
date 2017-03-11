<?php

namespace ReturnTypes;

class Foo extends FooParent implements FooInterface
{
    public function returnNothing()
    {
        return;
    }

    public function returnInteger(): int
    {
        return 1;
        return 'foo';
        $foo = function () {
            return 'bar';
        };
    }

    public function returnObject(): Bar
    {
        return 1;
        return new self();
        return new Bar();
    }

    public function returnChild(): self
    {
        return new self();
        return new FooChild();
        return new OtherInterfaceImpl();
    }

    /**
     * @return string|null
     */
    public function returnNullable()
    {
        return 'foo';
        return null;
    }

    public function returnInterface(): FooInterface
    {
        return new self();
    }

    /**
     * @return void
     */
    public function returnVoid()
    {
        return;
        return null;
        return 1;
    }

    /**
     * @return static
     */
    public function returnStatic(): FooParent
    {
        return parent::returnStatic();

        $parent = new FooParent();
        return $parent->returnStatic(); // the only case with wrong static base class
        return $this->returnStatic();
    }

    public function returnAlias(): Foo
    {
        return new FooAlias();
    }

    public function returnAnotherAlias(): FooAlias
    {
        return new Foo();
    }

    /**
     * @param self[]|Collection $collection
     * @return self[]|Collection
     */
    public function returnUnionIterableType($collection)
    {
        return $collection;
        return new Collection();
        return new self();
        return [new self()];
        return new Bar();
        return [new Bar()];
        return 1;
        return;

        /** @var Bar[]|Collection $barListOrCollection */
        $barListOrCollection = doFoo();
        return $barListOrCollection;

        /** @var self[]|AnotherCollection $selfListOrAnotherCollection */
        $selfListOrAnotherCollection = doFoo();
        return $selfListOrAnotherCollection;

        /** @var self[]|Collection|AnotherCollection $selfListOrCollectionorAnotherCollection */
        $selfListOrCollectionorAnotherCollection = doFoo();
        return $selfListOrCollectionorAnotherCollection;

        /** @var Bar[]|AnotherCollection $completelyDiffernetUnionIterable */
        $completelyDiffernetUnionIterable = doFoo();
        return $completelyDiffernetUnionIterable;

        return null;
    }

    /**
     * @param self[]|Collection $collection
     * @return self[]|Collection|OtherCollection|null
     */
    public function returnUnionIterableLooserReturnType($collection)
    {
        return $collection;
        return null;
    }

    /**
     * @return $this
     */
    public function returnThis(): self
    {
        return $this;
        return new self();
        return 1;
        return null;

        $that = $this;
        return $that;
    }

    /**
     * @return $this|null
     */
    public function returnThisOrNull()
    {
        return $this;
        return new self();
        return 1;
        return null;
        return $this->returnThis();
        return $this->returnStaticThatReturnsNewStatic();
    }

    /**
     * @return static
     */
    public function returnStaticThatReturnsNewStatic(): self
    {
        return new static();
        return $this;
    }

    public function returnsParent(): parent
    {
        return new FooParent();
        return 1;
        return null;
    }

    public function returnsPhpDocParent(): parent
    {
        return new FooParent();
        return 1;
        return null;
    }

    /**
     * @return scalar
     */
    public function returnScalar()
    {
        return 1;
        return 10.1;
        return 'a';
        return false;
        return new \stdClass();
    }
}

class FooChild extends Foo
{
}
