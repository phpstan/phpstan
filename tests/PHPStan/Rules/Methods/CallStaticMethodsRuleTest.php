<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;

class CallStaticMethodsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        $broker = $this->createBroker();
        return new CallStaticMethodsRule(
            $broker,
            new FunctionCallParametersCheck($broker, true)
        );
    }

    public function testCallStaticMethods()
    {
        $this->analyse([__DIR__ . '/data/call-static-methods.php'], [
            [
                'Call to an undefined static method CallStaticMethods\Foo::bar().',
                33,
            ],
            [
                'Call to an undefined static method CallStaticMethods\Bar::bar().',
                34,
            ],
            [
                'Call to an undefined static method CallStaticMethods\Foo::bar().',
                35,
            ],
            [
                'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
                36,
            ],
            [
                'Call to private static method dolor() of class CallStaticMethods\Foo.',
                37,
            ],
            [
                'CallStaticMethods\Ipsum::ipsumTest() calls parent::lorem() but CallStaticMethods\Ipsum does not extend any class.',
                54,
            ],
            [
                'Static method CallStaticMethods\Foo::test() invoked with 1 parameter, 0 required.',
                56,
            ],
            [
                'Call to protected static method baz() of class CallStaticMethods\Foo.',
                57,
            ],
            [
                'Call to static method loremIpsum() on an unknown class CallStaticMethods\UnknownStaticMethodClass.',
                58,
            ],
            [
                'Parent constructor invoked with 0 parameters, 1 required.',
                73,
            ],
            [
                'Calling self::someStaticMethod() outside of class scope.',
                78,
            ],
            [
                'Calling static::someStaticMethod() outside of class scope.',
                79,
            ],
            [
                'Calling parent::someStaticMethod() outside of class scope.',
                80,
            ],
            [
                'Call to protected static method baz() of class CallStaticMethods\Foo.',
                82,
            ],
            [
                'Call to an undefined static method CallStaticMethods\Foo::bar().',
                83,
            ],
            [
                'Static call to instance method CallStaticMethods\Foo::loremIpsum().',
                84,
            ],
            [
                'Call to private static method dolor() of class CallStaticMethods\Foo.',
                85,
            ],
        ]);
    }

    public function testCallInterfaceMethods()
    {
        $this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
            [
                'Call to an undefined static method Baz::barStaticMethod().',
                21,
            ],
        ]);
    }

    public function testCallToIncorrectCaseMethodName()
    {
        $this->analyse([__DIR__ . '/data/incorrect-static-method-case.php'], [
            [
                'Call to static method IncorrectStaticMethodCase\Foo::fooBar() with incorrect case: foobar',
                9,
            ],
        ]);
    }

    public function testStaticCallsToInstanceMethods()
    {
        $this->analyse([__DIR__ . '/data/static-calls-to-instance-methods.php'], [
            [
                'Static call to instance method StaticCallsToInstanceMethods\Foo::doFoo().',
                9,
            ],
            [
                'Static call to instance method StaticCallsToInstanceMethods\Bar::doBar().',
                15,
            ],
            [
                'Static call to instance method StaticCallsToInstanceMethods\Foo::doFoo().',
                31,
            ],
            [
                'Call to method StaticCallsToInstanceMethods\Foo::doFoo() with incorrect case: dofoo',
                37,
            ],
            [
                'Method StaticCallsToInstanceMethods\Foo::doFoo() invoked with 1 parameter, 0 required.',
                38,
            ],
            [
                'Call to private method doPrivateFoo() of class StaticCallsToInstanceMethods\Foo.',
                40,
            ],
            [
                'Method StaticCallsToInstanceMethods\Foo::doFoo() invoked with 1 parameter, 0 required.',
                43,
            ],
        ]);
    }
}
