<?php

namespace InterfaceAncestorsExtends;

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
 * @extends FooGeneric<int, \InvalidArgumentException>
 */
interface FooDoesNotImplementAnything
{

}

/**
 * @extends FooGeneric<int, \InvalidArgumentException>
 * @extends FooGeneric2<int, \InvalidArgumentException>
 */
interface FooInvalidImplementsTags extends FooGeneric
{

}

/**
 * @extends FooGeneric2<int, \InvalidArgumentException>
 */
interface FooWrongClassImplemented extends FooGeneric, FooGeneric3
{

}

/**
 * @extends class-string<T>
 */
interface FooWrongTypeInImplementsTag extends FooGeneric
{

}

/**
 * @extends FooGeneric<int, \InvalidArgumentException>
 */
interface FooCorrect extends FooGeneric
{

}

/**
 * @extends FooGeneric<int>
 */
interface FooNotEnough extends FooGeneric
{

}

/**
 * @extends FooGeneric<int, \InvalidArgumentException, string>
 */
interface FooExtraTypes extends FooGeneric
{

}

/**
 * @extends FooGeneric<int, \Throwable>
 */
interface FooNotSubtype extends FooGeneric
{

}

/**
 * @extends FooGeneric<int, \stdClass>
 */
interface FooAlsoNotSubtype extends FooGeneric
{

}

/**
 * @extends FooGeneric<Zazzuuuu, \Exception>
 */
interface FooUnknowninterface extends FooGeneric
{

}

/**
 * @template T
 * @extends FooGeneric<int, T>
 */
interface FooGenericGeneric extends FooGeneric
{

}

/**
 * @template T of \Throwable
 * @extends FooGeneric<int, T>
 */
interface FooGenericGeneric2 extends FooGeneric
{

}


/**
 * @template T of \Exception
 * @extends FooGeneric<int, T>
 */
interface FooGenericGeneric3 extends FooGeneric
{

}

/**
 * @template T of \InvalidArgumentException
 * @extends FooGeneric<int, T>
 */
interface FooGenericGeneric4 extends FooGeneric
{

}

/**
 * @template T
 * @extends FooGeneric<T, \Exception>
 */
interface FooGenericGeneric5 extends FooGeneric
{

}

/**
 * @template T of \stdClass
 * @extends FooGeneric<T, \Exception>
 */
interface FooGenericGeneric6 extends FooGeneric
{

}

/**
 * @template T of \stdClass
 * @extends FooGeneric<int, T>
 */
interface FooGenericGeneric7 extends FooGeneric
{

}

/**
 * @template T of \stdClass
 * @extends FooGeneric<int, T>
 * @extends FooGeneric2<int, T>
 */
interface FooGenericGeneric8 extends FooGeneric, FooGeneric2
{

}
