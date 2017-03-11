<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Rules\FunctionCallParametersCheck;

class CallToFunctionParametersRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        $broker = $this->createBroker();
        return new CallToFunctionParametersRule(
            $broker,
            new FunctionCallParametersCheck($broker, true)
        );
    }

    public function testCallToFunctionWithoutParameters()
    {
        require_once __DIR__ . '/data/existing-function-definition.php';
        $this->analyse([__DIR__ . '/data/existing-function.php'], []);
    }

    public function testCallToFunctionWithBadNumberOfParameters()
    {
        require_once __DIR__ . '/data/incorrect-call-to-function-definition.php';
        $this->analyse([__DIR__ . '/data/incorrect-call-to-function.php'], [
            [
                'Function IncorrectCallToFunction\foo invoked with 1 parameter, 2 required.',
                5,
            ],
            [
                'Function IncorrectCallToFunction\foo invoked with 3 parameters, 2 required.',
                7,
            ],
        ]);
    }

    public function testCallToFunctionWithOptionalParameters()
    {
        require_once __DIR__ . '/data/call-to-function-with-optional-parameters-definition.php';
        $this->analyse([__DIR__ . '/data/call-to-function-with-optional-parameters.php'], [
            [
                'Function CallToFunctionWithOptionalParameters\foo invoked with 3 parameters, 1-2 required.',
                9,
            ],
        ]);
    }

    public function testCallToFunctionWithDynamicParameters()
    {
        require_once __DIR__ . '/data/function-with-variadic-parameters-definition.php';
        $this->analyse([__DIR__ . '/data/function-with-variadic-parameters.php'], [
            [
                'Function FunctionWithVariadicParameters\foo invoked with 0 parameters, at least 1 required.',
                5,
            ],
            [
                'Function FunctionWithVariadicParameters\bar invoked with 0 parameters, at least 1 required.',
                9,
            ],
        ]);
    }

    public function testCallToArrayUnique()
    {
        $this->analyse([__DIR__ . '/data/call-to-array-unique.php'], [
            [
                'Function array_unique invoked with 3 parameters, 1-2 required.',
                3,
            ],
        ]);
    }

    public function testCallToArrayMapVariadic()
    {
        $this->analyse([__DIR__ . '/data/call-to-array-map-unique.php'], []);
    }

    public function testCallToWeirdFunctions()
    {
        $this->analyse([__DIR__ . '/data/call-to-weird-functions.php'], [
            [
                'Function implode invoked with 0 parameters, 1-2 required.',
                3,
            ],
            [
                'Function implode invoked with 3 parameters, 1-2 required.',
                6,
            ],
            [
                'Function strtok invoked with 0 parameters, 1-2 required.',
                8,
            ],
            [
                'Function strtok invoked with 3 parameters, 1-2 required.',
                11,
            ],
            [
                'Function fputcsv invoked with 1 parameter, 2-5 required.',
                13,
            ],
            [
                'Function imagepng invoked with 0 parameters, 1-4 required.',
                16,
            ],
            [
                'Function imagepng invoked with 5 parameters, 1-4 required.',
                19,
            ],
        ]);
    }

    /**
     * @requires PHP 7.1.1
     */
    public function testUnpackOnAfter711()
    {
        $this->analyse([__DIR__ . '/data/unpack.php'], [
            [
                'Function unpack invoked with 0 parameters, 2-3 required.',
                3,
            ],
        ]);
    }

    public function testUnpackOnBefore711()
    {
        if (PHP_VERSION_ID >= 70101) {
            $this->markTestSkipped('This test requires PHP < 7.1.1');
        }
        $this->analyse([__DIR__ . '/data/unpack.php'], [
            [
                'Function unpack invoked with 0 parameters, 2 required.',
                3,
            ],
            [
                'Function unpack invoked with 3 parameters, 2 required.',
                4,
            ],
        ]);
    }
}
