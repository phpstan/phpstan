<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

class CatchedExceptionExistenceRuleTest extends \PHPStan\Rules\AbstractRuleTest
{
    protected function getRule(): \PHPStan\Rules\Rule
    {
        return new CatchedExceptionExistenceRule(
            $this->createBroker()
        );
    }

    public function testCheckCatchedException()
    {
        $this->analyse([__DIR__ . '/data/catch.php'], [
            [
                'Catched class TestCatch\FooCatch is not an exception.',
                14,
            ],
            [
                'Catched class FooCatchException not found.',
                22,
            ],
        ]);
    }
}
