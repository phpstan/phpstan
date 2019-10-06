<?php

namespace InterfaceAncestorsImplements;

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
interface FooDoesNotImplementAnything
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException>
 * @implements FooGeneric2<int, \InvalidArgumentException>
 */
interface FooInvalidImplementsTags
{

}

/**
 * @implements FooGeneric2<int, \InvalidArgumentException>
 */
interface FooWrongClassImplemented
{

}

/**
 * @implements class-string<T>
 */
interface FooWrongTypeInImplementsTag
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException>
 */
interface FooCorrect
{

}

/**
 * @implements FooGeneric<int>
 */
interface FooNotEnough
{

}

/**
 * @implements FooGeneric<int, \InvalidArgumentException, string>
 */
interface FooExtraTypes
{

}

/**
 * @implements FooGeneric<int, \Throwable>
 */
interface FooNotSubtype
{

}

/**
 * @implements FooGeneric<int, \stdClass>
 */
interface FooAlsoNotSubtype
{

}

/**
 * @implements FooGeneric<Zazzuuuu, \Exception>
 */
interface FooUnknownClass
{

}

/**
 * @template T
 * @implements FooGeneric<int, T>
 */
interface FooGenericGeneric
{

}

/**
 * @template T of \Throwable
 * @implements FooGeneric<int, T>
 */
interface FooGenericGeneric2
{

}


/**
 * @template T of \Exception
 * @implements FooGeneric<int, T>
 */
interface FooGenericGeneric3
{

}

/**
 * @template T of \InvalidArgumentException
 * @implements FooGeneric<int, T>
 */
interface FooGenericGeneric4
{

}

/**
 * @template T
 * @implements FooGeneric<T, \Exception>
 */
interface FooGenericGeneric5
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<T, \Exception>
 */
interface FooGenericGeneric6
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<int, T>
 */
interface FooGenericGeneric7
{

}

/**
 * @template T of \stdClass
 * @implements FooGeneric<int, T>
 * @implements FooGeneric2<int, T>
 */
interface FooGenericGeneric8
{

}
