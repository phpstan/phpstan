<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;

class CallMethodsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{

    /** @var bool */
    private $checkThisOnly;

    protected function getRule(): Rule
    {
        $broker = $this->createBroker();
        return new CallMethodsRule(
            $broker,
            new FunctionCallParametersCheck($broker, true),
            new RuleLevelHelper(),
            $this->checkThisOnly
        );
    }

    public function testCallMethods()
    {
        $this->checkThisOnly = false;
        $this->analyse([ __DIR__ . '/data/call-methods.php'], [
            [
                'Call to an undefined method Test\Foo::protectedMethodFromChild().',
                9,
            ],
            [
                'Call to an undefined method Test\Bar::loremipsum().',
                33,
            ],
            [
                'Call to private method foo() of class Test\Foo.',
                34,
            ],
            [
                'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
                39,
            ],
            [
                'Cannot call method method() on string.',
                42,
            ],
            [
                'Call to method doFoo() on an unknown class Test\UnknownClass.',
                56,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                59,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                61,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                63,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                65,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                68,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                69,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                70,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                71,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                72,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                74,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                76,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                77,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                78,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                79,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                83,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                84,
            ],
            [
                'Call to an undefined method ArrayObject::doFoo().',
                100,
            ],
            [
                'Method PDO::query() invoked with 0 parameters, 1-4 required.',
                105,
            ],
        ]);
    }

    public function testCallMethodsOnThisOnly()
    {
        $this->checkThisOnly = true;
        $this->analyse([ __DIR__ . '/data/call-methods.php'], [
            [
                'Call to an undefined method Test\Foo::protectedMethodFromChild().',
                9,
            ],
            [
                'Call to an undefined method Test\Bar::loremipsum().',
                33,
            ],
            [
                'Call to private method foo() of class Test\Foo.',
                34,
            ],
            [
                'Method Test\Foo::test() invoked with 0 parameters, 1 required.',
                39,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                59,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                61,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                63,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                65,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                68,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                69,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                70,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                71,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                72,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                74,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                76,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                77,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                78,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                79,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                83,
            ],
            [
                'Result of method Test\Bar::returnsVoid() (void) is used.',
                84,
            ],
        ]);
    }

    public function testCallTraitMethods()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/call-trait-methods.php'], [
            [
                'Call to an undefined method Baz::unexistentMethod().',
                20,
            ],
        ]);
    }

    public function testCallInterfaceMethods()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/call-interface-methods.php'], [
            [
                'Call to an undefined method Baz::barMethod().',
                19,
            ],
        ]);
    }

    public function testClosureBind()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/closure-bind.php'], [
            [
                'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
                11,
            ],
            [
                'Call to an undefined method CallClosureBind\Bar::barMethod().',
                15,
            ],
            [
                'Call to private method privateMethod() of class CallClosureBind\Foo.',
                17,
            ],
            [
                'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
                18,
            ],
            [
                'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
                27,
            ],
            [
                'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
                32,
            ],
            [
                'Call to an undefined method CallClosureBind\Foo::nonexistentMethod().',
                38,
            ],
        ]);
    }

    public function testCallVariadicMethods()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/call-variadic-methods.php'], [
            [
                'Method CallVariadicMethods\Foo::baz() invoked with 0 parameters, at least 1 required.',
                9,
            ],
            [
                'Method CallVariadicMethods\Foo::lorem() invoked with 0 parameters, at least 2 required.',
                10,
            ],
            [
                'Parameter #2 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
                30,
            ],
            [
                'Parameter #3 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
                30,
            ],
            [
                'Parameter #1 $int of method CallVariadicMethods\Foo::doVariadicString() expects int, string given.',
                32,
            ],
            [
                'Parameter #3 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string, int given.',
                40,
            ],
            [
                'Parameter #4 ...$strings of method CallVariadicMethods\Foo::doVariadicString() expects string[], int[] given.',
                40,
            ],
        ]);
    }

    public function testCallToIncorrectCaseMethodName()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/incorrect-method-case.php'], [
            [
                'Call to method IncorrectMethodCase\Foo::fooBar() with incorrect case: foobar',
                9,
            ],
        ]);
    }

    /**
     * @requires PHP 7.1
     */
    public function testNullableParameters()
    {
        if (self::isObsoletePhpParserVersion()) {
            $this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
        }
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/nullable-parameters.php'], [
            [
                'Method NullableParameters\Foo::doFoo() invoked with 0 parameters, 2 required.',
                6,
            ],
            [
                'Method NullableParameters\Foo::doFoo() invoked with 1 parameter, 2 required.',
                7,
            ],
            [
                'Method NullableParameters\Foo::doFoo() invoked with 3 parameters, 2 required.',
                10,
            ],
        ]);
    }

    public function testProtectedMethodCallFromParent()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/protected-method-call-from-parent.php'], []);
    }

    public function testSiblingMethodPrototype()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/sibling-method-prototype.php'], []);
    }

    public function testOverridenMethodPrototype()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/overriden-method-prototype.php'], []);
    }

    public function testCallMethodWithInheritDoc()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/calling-method-with-inheritdoc.php'], [
            [
                'Parameter #1 $i of method MethodWithInheritDoc\Baz::doFoo() expects int, string given.',
                57,
            ],
            [
                'Parameter #1 $str of method MethodWithInheritDoc\Foo::doBar() expects string, int given.',
                59,
            ],
        ]);
    }

    public function testNegatedInstanceof()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/negated-instanceof.php'], []);
    }

    public function testInvokeMagicInvokeMethod()
    {
        $this->checkThisOnly = false;
        $this->analyse([__DIR__ . '/data/invoke-magic-method.php'], [
            [
                'Parameter #1 $foo of method InvokeMagicInvokeMethod\ClassForCallable::doFoo() expects callable, InvokeMagicInvokeMethod\ClassForCallable given.',
                22,
            ],
        ]);
    }
}
