<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ThisType extends StaticType
{

    public function describe(VerbosityLevel $level): string
    {
        return sprintf('$this(%s)', $this->getStaticObjectType()->describe($level));
    }

    public function accepts(Type $type, bool $strictTypes): TrinaryLogic
    {
        return TrinaryLogic::createFromBoolean(
            $type instanceof self
            && $type->getBaseClass() === $this->getBaseClass()
        );
    }
}
