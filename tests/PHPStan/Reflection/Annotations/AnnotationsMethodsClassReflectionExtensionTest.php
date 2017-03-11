<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Annotations;

use PHPStan\Broker\Broker;

class AnnotationsMethodsClassReflectionExtensionTest extends \PHPStan\TestCase
{
    public function dataMethods(): array
    {
        return [
            [
                \AnnotationsMethods\Foo::class,
                [
                    'getInteger' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'int',
                    ],
                    'doSomething' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'void',
                    ],
                    'getFooOrBar' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
                    ],
                    'methodWithNoReturnType' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'mixed',
                    ],
                ],
            ],
            [
                \AnnotationsMethods\Bar::class,
                [
                    'getInteger' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'int',
                    ],
                    'doSomething' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'void',
                    ],
                    'getFooOrBar' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
                    ],
                    'methodWithNoReturnType' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'mixed',
                    ],
                ],
            ],
            [
                \AnnotationsMethods\Baz::class,
                [
                    'getInteger' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'int',
                    ],
                    'doSomething' => [
                        'class' => \AnnotationsMethods\Baz::class,
                        'returnType' => 'void',
                    ],
                    'getFooOrBar' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
                    ],
                    'getIpsum' => [
                        'class' => \AnnotationsMethods\Baz::class,
                        'returnType' => 'OtherNamespace\Ipsum',
                    ],
                    'methodWithNoReturnType' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'mixed',
                    ],
                ],
            ],
            [
                \AnnotationsMethods\BazBaz::class,
                [
                    'getInteger' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'int',
                    ],
                    'doSomething' => [
                        'class' => \AnnotationsMethods\Baz::class,
                        'returnType' => 'void',
                    ],
                    'getFooOrBar' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'AnnotationsMethods\Foo|AnnotationsMethods\Bar',
                    ],
                    'getIpsum' => [
                        'class' => \AnnotationsMethods\Baz::class,
                        'returnType' => 'OtherNamespace\Ipsum',
                    ],
                    'methodWithNoReturnType' => [
                        'class' => \AnnotationsMethods\Foo::class,
                        'returnType' => 'mixed',
                    ],
                    'getTest' => [
                        'class' => \AnnotationsMethods\BazBaz::class,
                        'returnType' => 'OtherNamespace\Test',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataMethods
     * @param string $className
     * @param mixed[] $methods
     */
    public function testMethods(string $className, array $methods)
    {
        /** @var Broker $broker */
        $broker = $this->getContainer()->get(Broker::class);
        $class = $broker->getClass($className);
        foreach ($methods as $methodName => $expectedMethodData) {
            $this->assertTrue($class->hasMethod($methodName), sprintf('Method %s not found in class %s.', $methodName, $className));

            $method = $class->getMethod($methodName);
            $this->assertSame(
                $expectedMethodData['class'],
                $method->getDeclaringClass()->getName(),
                sprintf('Declaring class of method $%s does not match.', $methodName)
            );
            $this->assertEquals(
                $expectedMethodData['returnType'],
                $method->getReturnType()->describe(),
                sprintf('Return type of method %s::%s does not match', $className, $methodName)
            );
        }
    }
}
