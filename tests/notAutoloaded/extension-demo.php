<?php
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use PHPStan\Type\IntegerType;

class FooProperty implements PropertyReflection
{
    /**
     * @var ClassReflection
     */
    private $classReflection;

    public function __construct(ClassReflection $classReflection)
    {
        $this->classReflection = $classReflection;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->classReflection;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return true;
    }

    public function isPublic(): bool
    {
        return false;
    }

    public function getType() : Type
    {
        return new IntegerType(false);
    }
}

class FooPropertiesClassReflectionExtension implements PropertiesClassReflectionExtension
{
    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        return true;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        return new FooProperty($classReflection);
    }
}

class Note
{
    public function __construct($a, $b)
    {
        $this->$a = $b;
    }

    public function bar() : string
    {
        return 1;
    }

    public function foo() : string
    {
        $this->bar(1,2,3);
        return self::$c;
    }
}
