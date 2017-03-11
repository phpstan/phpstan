<?php

namespace MethodPhpDocsNamespace;

use SomeNamespace\Amet as Dolor;
use SomeNamespace\Consecteur;

class Foo extends FooParent
{

    /**
     * @return Bar
     */
    public static function doSomethingStatic()
    {
    }

    /**
     * @param Foo|Bar $unionTypeParameter
     * @param int $anotherMixedParameter
     * @param int $anotherMixedParameter
     * @paran int $yetAnotherMixedProperty
     * @param int $integerParameter
     * @param integer $anotherIntegerParameter
     * @param aRray $arrayParameterOne
     * @param mixed[] $arrayParameterOther
     * @param Lorem $objectRelative
     * @param \SomeOtherNamespace\Ipsum $objectFullyQualified
     * @param Dolor $objectUsed
     * @param null|int $nullableInteger
     * @param Dolor|null $nullableObject
     * @param Dolor $anotherNullableObject
     * @param self $selfType
     * @param static $staticType
     * @param Null $nullType
     * @param Bar $barObject
     * @param Foo $conflictedObject
     * @param Baz $moreSpecifiedObject
     * @param resource $resource
     * @param array[array] $yetAnotherAnotherMixedParameter
     * @param \\Test\Bar $yetAnotherAnotherAnotherMixedParameter
     * @param New $yetAnotherAnotherAnotherAnotherMixedParameter
     * @param void $voidParameter
     * @param Consecteur $useWithoutAlias
     * @param true $true
     * @param false $false
     * @param true $boolTrue
     * @param false $boolFalse
     * @param bool $trueBoolean
     * @return Foo
     */
    public function doFoo(
        $mixedParameter,
        $unionTypeParameter,
        $anotherMixedParameter,
        $yetAnotherMixedParameter,
        $integerParameter,
        $anotherIntegerParameter,
        $arrayParameterOne,
        $arrayParameterOther,
        $objectRelative,
        $objectFullyQualified,
        $objectUsed,
        $nullableInteger,
        $nullableObject,
        $anotherNullableObject = null,
        $selfType,
        $staticType,
        $nullType,
        $barObject,
        Bar $conflictedObject,
        Bar $moreSpecifiedObject,
        $resource,
        $yetAnotherAnotherMixedParameter,
        $yetAnotherAnotherAnotherMixedParameter,
        $yetAnotherAnotherAnotherAnotherMixedParameter,
        $voidParameter,
        $useWithoutAlias,
        $true,
        $false,
        bool $boolTrue,
        bool $boolFalse,
        bool $trueBoolean
    ) {
        $parent = new FooParent();
        $differentInstance = new self();
        foreach ($moreSpecifiedObject->doFluentUnionIterable() as $fluentUnionIterableBaz) {
            die;
        }
    }

    /**
     * @return self[]
     */
    public function doBar(): array
    {
    }

    public function returnParent(): parent
    {
    }

    /**
     * @return parent
     */
    public function returnPhpDocParent()
    {
    }

    /**
     * @return NULL[]
     */
    public function returnNulls(): array
    {
    }
}
