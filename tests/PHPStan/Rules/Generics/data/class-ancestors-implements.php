<?php

namespace ClassAncestorsImplements;

/**
 * @template T
 * @template U of \Exception
 */
interface FooGeneric
{

}

/**
 * @template T
 * @template V of \Exception
 */
interface FooGeneric2
{

}

/**
 * @template T
 * @template W of \Exception
 */
interface FooGeneric3
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException>
 */
class FooDoesNotImplementAnything
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException>
 * @implements FooGeneric2<int, \InvalidArgumentException>
 */
class FooInvalidImplementsTags implements FooGeneric
{

}

/**
 * @implements FooGeneric2<int, \InvalidArgumentException>
 */
class FooWrongClassImplemented implements FooGeneric, FooGeneric3
{

}

/**
 * @implements class-string<T>
 */
class FooWrongTypeInImplementsTag implements FooGeneric
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException>
 */
class FooCorrect implements FooGeneric
{

}

/**
 * @implements FooGeneric<int>
 */
class FooNotEnough implements FooGeneric
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException, string>
 */
class FooExtraTypes implements FooGeneric
{

}

/**
 * @implements FooGeneric<int, \Throwable>
 */
class FooNotSubtype implements FooGeneric
{

}

/**
 * @implements FooGeneric<int, \stdClass>
 */
class FooAlsoNotSubtype implements FooGeneric
{

}

/**
 * @implements FooGeneric<Zazzuuuu, \Exception>
 */
class FooUnknownClass implements FooGeneric
{

}

/**
 * @template T
 * @implements FooGeneric<int, T>
 */
class FooGenericGeneric implements FooGeneric
{

}

/**
 * @template T of \Throwable
 * @implements FooGeneric<int, T>
 */
class FooGenericGeneric2 implements FooGeneric
{

}


/**
 * @template T of \Exception
 * @implements FooGeneric<int, T>
 */
class FooGenericGeneric3 implements FooGeneric
{

}

/**
 * @template T of \InvalidArgumentException
 * @implements FooGeneric<int, T>
 */
class FooGenericGeneric4 implements FooGeneric
{

}

/**
 * @template T
 * @implements FooGeneric<T, \Exception>
 */
class FooGenericGeneric5 implements FooGeneric
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<T, \Exception>
 */
class FooGenericGeneric6 implements FooGeneric
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<int, T>
 */
class FooGenericGeneric7 implements FooGeneric
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<int, T>
 * @implements FooGeneric2<int, T>
 */
class FooGenericGeneric8 implements FooGeneric, FooGeneric2
{

}
