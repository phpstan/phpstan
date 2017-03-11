<?php

namespace UnusedConstructorParameters;

class Foo
{
    private $foo;
    private $bar;

    public function __construct(
        $usedParameter,
        $usedParameterInStringOne,
        $usedParameterInStringTwo,
        $usedParameterInStringThree,
        $usedParameterInCondition,
        $usedParameterInClosureUse,
        $usedParameterInCompact,
        $unusedParameter,
        $anotherUnusedParameter
    ) {
        $this->foo = $usedParameter;
        var_dump("Hello $usedParameterInStringOne");
        var_dump("Hello {$usedParameterInStringTwo}");
        var_dump("Hello ${usedParameterInStringThree}");
        if (doFoo()) {
            $this->foo = $usedParameterInCondition;
        }
        function ($anotherUnusedParameter) use ($usedParameterInClosureUse) {
            echo $anotherUnusedParameter; // different scope
        };
        $this->bar = compact('usedParameterInCompact');
    }
}

interface Bar
{
    public function __construct($interfaceParameter);
}
