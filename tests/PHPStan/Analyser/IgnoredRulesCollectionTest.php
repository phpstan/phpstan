<?php
declare(strict_types=1);

namespace PHPStan\Analyser;

use PHPUnit\Framework\TestCase;

final class IgnoredRulesCollectionTest extends TestCase
{

    /**
     * Test the detection for ignored rules
     *
     * @param int $line
     * @param bool $expectErrorToBeIgnored
     *
     * @dataProvider errorNodesDataProvider
     *
     * @return void
     */
    public function testIsIgnoredCheck(int $line, bool $expectErrorToBeIgnored): void
    {
        $methodNode = new \PhpParser\Node\Stmt\ClassMethod(
            'helloWorld',
            [],
            ['startLine' => 4, 'endLine' => 19]
        );

        $subject = new IgnoredRulesCollection();
        $subject->add($methodNode, 'PHPStan.Rules.Functions.CallToNonExistentFunctionRule');

        $callToNonExistingMethodNode = new \PhpParser\Node\Expr\FuncCall(
            new \PhpParser\Node\Name('doSomething'),
            [],
            ['startLine' => $line, 'endLine' => $line]
        );

        $this->assertSame(
            $expectErrorToBeIgnored,
            $subject->isIgnored($callToNonExistingMethodNode, 'PHPStan.Rules.Functions.CallToNonExistentFunctionRule')
        );
    }

    public function errorNodesDataProvider(): array
    {
        return [
            'ignored error'                => ['line' => 10, 'expectErrorToBeIgnored' => true],
            'ignored error at lower bound' => ['line' => 4, 'expectErrorToBeIgnored' => true],
            'ignored error at upper bound' => ['line' => 19, 'expectErrorToBeIgnored' => true],
            'error before comment'         => ['line' => 3, 'expectErrorToBeIgnored' => false],
            'error after comment'          => ['line' => 20, 'expectErrorToBeIgnored' => false],
        ];
    }

}