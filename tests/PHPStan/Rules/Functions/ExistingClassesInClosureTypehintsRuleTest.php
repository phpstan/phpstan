<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionDefinitionCheck;

class ExistingClassesInClosureTypehintsRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new ExistingClassesInClosureTypehintsRule(new FunctionDefinitionCheck($this->createBroker()));
    }

    public function testExistingClassInTypehint()
    {
        $this->analyse([__DIR__ . '/data/closure-typehints.php'], [
            [
                'Return typehint of anonymous function has invalid type TestClosureFunctionTypehints\NonexistentClass.',
                9,
            ],
            [
                'Parameter $bar of anonymous function has invalid typehint type TestClosureFunctionTypehints\BarFunctionTypehints.',
                12,
            ],
            [
                'Return typehint of anonymous function has invalid type parent.',
                18,
            ],
        ]);
    }

    /**
     * @requires PHP 7.1
     */
    public function testValidTypehint()
    {
        if (self::isObsoletePhpParserVersion()) {
            $this->markTestSkipped('Test requires PHP-Parser ^3.0.0');
        }
        $this->analyse([__DIR__ . '/data/closure-7.1-typehints.php'], [
            [
                'Parameter $bar of anonymous function has invalid typehint type TestClosureFunctionTypehintsPhp71\NonexistentClass.',
                24,
            ],
            [
                'Return typehint of anonymous function has invalid type TestClosureFunctionTypehintsPhp71\NonexistentClass.',
                24,
            ],
        ]);
    }
}
