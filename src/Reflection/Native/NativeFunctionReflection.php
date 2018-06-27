<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Type\Type;

class NativeFunctionReflection implements \PHPStan\Reflection\FunctionReflection
{

    /** @var string */
    private $name;

    /** @var \PHPStan\Reflection\ParametersAcceptor[] */
    private $variants;

    /** @var \PHPStan\Type\Type|null */
    private $throwType;

    /** @var bool */
    private $isDeprecated;

    /**
     * @param string $name
     * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
     * @param \PHPStan\Type\Type|null $throwType
     * @param bool $isDeprecated
     */
    public function __construct(
        string $name,
        array $variants,
        ?Type $throwType,
        bool $isDeprecated
    ) {
        $this->name = $name;
        $this->variants = $variants;
        $this->throwType = $throwType;
        $this->isDeprecated = $isDeprecated;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \PHPStan\Reflection\ParametersAcceptor[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    public function getThrowType(): ?Type
    {
        return $this->throwType;
    }

    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }
}
