<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Type\ErrorType;
use PHPStan\Type\VerbosityLevel;

class InvalidCastRule implements \PHPStan\Rules\Rule
{

    /** @var \PHPStan\Broker\Broker */
    private $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function getNodeType(): string
    {
        return \PhpParser\Node\Expr\Cast::class;
    }

    /**
     * @param \PhpParser\Node\Expr\Cast $node
     * @param \PHPStan\Analyser\Scope $scope
     * @return string[] errors
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($scope->getType($node) instanceof ErrorType) {
            $classReflection = $this->broker->getClass(get_class($node));
            $shortName = $classReflection->getNativeReflection()->getShortName();
            $shortName = strtolower($shortName);
            if ($shortName === 'double') {
                $shortName = 'float';
            } else {
                $shortName = substr($shortName, 0, -1);
            }

            return [
                sprintf(
                    'Cannot cast %s to %s.',
                    $scope->getType($node->expr)->describe(VerbosityLevel::value()),
                    $shortName
                ),
            ];
        }

        return [];
    }
}
