<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\Error;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\File\FileExcluder;
use PHPStan\File\FileHelper;
use PHPStan\Type\FileTypeMapper;

abstract class AbstractRuleTest extends \PHPStan\TestCase
{

    /** @var \PHPStan\Analyser\Analyser */
    private $analyser;

    /**
     * @var \PHPStan\File\FileHelper
     */
    private $fileHelper;

    abstract protected function getRule(): Rule;

    private function getAnalyser(): Analyser
    {
        if ($this->analyser === null) {
            $registry = new Registry([
                $this->getRule(),
            ]);

            $broker = $this->createBroker();
            $printer = new \PhpParser\PrettyPrinter\Standard();
            $fileHelper = $this->getFileHelper();
            $fileExcluder = new FileExcluder($fileHelper, []);
            $this->analyser = new Analyser(
                $broker,
                $this->getParser(),
                $registry,
                new NodeScopeResolver(
                    $broker,
                    $this->getParser(),
                    $printer,
                    new FileTypeMapper($this->getParser(), $this->createMock(\Psr\Cache\CacheItemPoolInterface::class), true),
                    new TypeSpecifier($printer),
                    $fileExcluder,
                    false,
                    false,
                    false,
                    []
                ),
                $printer,
                $fileExcluder,
                [],
                null,
                $fileHelper,
                true
            );
        }

        return $this->analyser;
    }

    private function getFileHelper(): FileHelper
    {
        if ($this->fileHelper === null) {
            $this->fileHelper = $this->getContainer()->getByType(FileHelper::class);
        }

        return $this->fileHelper;
    }

    private function assertError(string $message, string $file, bool $exactMatch, int $line = null, Error $error)
    {
        $this->assertSame($this->getFileHelper()->normalizePath($file), $error->getFile(), $error->getMessage());
        $this->assertSame($line, $error->getLine(), $error->getMessage());

        if ($exactMatch) {
            $this->assertSame($message, $error->getMessage());
        } else {
            $this->assertContains($message, $error->getMessage());
        }
    }

    public function analyse(array $files, array $errors)
    {
        $result = $this->getAnalyser()->analyse($files, false);
        $this->assertInternalType('array', $result);
        foreach ($errors as $i => $error) {
            if (!isset($result[$i])) {
                $this->fail(
                    sprintf(
                        'Expected %d errors, but result contains only %d. Looking for error message: %s',
                        count($errors),
                        count($result),
                        $error[0]
                    )
                );
            }

            $this->assertError($error[0], $files[0], $error[2] ?? true, $error[1], $result[$i]);
        }

        $this->assertCount(
            count($errors),
            $result,
            sprintf(
                'Expected only %d errors, but result contains %d.',
                count($errors),
                count($result)
            )
        );
    }
}
