<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Traits\ConstantScalarTypeTrait;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ConstantFloatType extends FloatType implements ConstantScalarType
{

    use ConstantScalarTypeTrait;
    use ConstantScalarToBooleanTrait;

    /** @var float */
    private $value;

    public function __construct(float $value)
    {
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function describe(VerbosityLevel $level): string
    {
        return $level->handle(
            function (): string {
                return 'float';
            },
            function (): string {
                $formatted = (string) $this->value;
                if (strpos($formatted, '.') === false) {
                    $formatted .= '.0';
                }

                return $formatted;
            }
        );
    }

    public function toString(): Type
    {
        return new ConstantStringType((string) $this->value);
    }

    public function toInteger(): Type
    {
        return new ConstantIntegerType((int) $this->value);
    }

    /**
     * @param mixed[] $properties
     * @return Type
     */
    public static function __set_state(array $properties): Type
    {
        return new self($properties['value']);
    }
}
