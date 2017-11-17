<?php

namespace ExtendsImplements;

class Foo
{

}

class Bar extends Foo implements FooInterface
{

}

class Baz extends FOO implements FOOInterface
{

}

interface FooInterface
{

}

interface BarInterface extends FooInterface
{

}

interface BazInterface extends FOOInterface
{

}
