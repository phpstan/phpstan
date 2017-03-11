<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

class DefinedVariableRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new DefinedVariableRule();
    }

    public function testDefinedVariables()
    {
        require_once __DIR__ . '/data/defined-variables-definition.php';
        $this->analyse([__DIR__ . '/data/defined-variables.php'], [
            [
                'Undefined variable: $definedLater',
                5,
            ],
            [
                'Undefined variable: $definedInIfOnly',
                10,
            ],
            [
                'Undefined variable: $definedInCases',
                21,
            ],
            [
                'Undefined variable: $fooParameterBeforeDeclaration',
                29,
            ],
            [
                'Undefined variable: $parseStrParameter',
                34,
            ],
            [
                'Undefined variable: $foo',
                39,
            ],
            [
                'Undefined variable: $willBeUnset',
                44,
            ],
            [
                'Undefined variable: $mustAlreadyExistWhenDividing',
                50,
            ],
            [
                'Undefined variable: $arrayDoesNotExist',
                58,
            ],
            [
                'Undefined variable: $undefinedVariable',
                60,
            ],
            [
                'Undefined variable: $this',
                97,
            ],
            [
                'Undefined variable: $this',
                100,
            ],
            [
                'Undefined variable: $variableInEmpty',
                144,
            ],
            [
                'Undefined variable: $negatedVariableInEmpty',
                155,
            ],
            [
                'Undefined variable: $http_response_header',
                184,
            ],
            [
                'Undefined variable: $http_response_header',
                190,
            ],
            [
                'Undefined variable: $assignedInKey',
                202,
            ],
            [
                'Undefined variable: $assignedInKey',
                203,
            ],
        ]);
    }

    /**
     * @requires PHP 7.1
     */
    public function testDefinedVariablesInShortArrayDestructuringSyntax()
    {
        if (self::isObsoletePhpParserVersion()) {
            $this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
        }
        $this->analyse([__DIR__ . '/data/defined-variables-array-destructuring-short-syntax.php'], [
            [
                'Undefined variable: $f',
                10,
            ],
            [
                'Undefined variable: $f',
                13,
            ],
            [
                'Undefined variable: $var3',
                31,
            ],
        ]);
    }
}
