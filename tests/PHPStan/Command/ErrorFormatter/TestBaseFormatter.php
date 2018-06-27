<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

abstract class TestBaseFormatter extends \PHPStan\Testing\TestCase
{

    private const DIRECTORY_PATH = '/data/folder/with space/and unicode 😃/project';

    /** @var StreamOutput */
    private $outputStream;

    /** @var ErrorsConsoleStyle */
    private $errorConsoleStyle;

    protected function setUp(): void
    {
        parent::setUp();

        $resource = fopen('php://memory', 'w', false);
        if ($resource === false) {
            throw new \PHPStan\ShouldNotHappenException();
        }

        $this->outputStream = new StreamOutput($resource);

        $this->errorConsoleStyle = new ErrorsConsoleStyle(new StringInput(''), $this->outputStream);
    }

    protected function getOutputContent(): string
    {
        rewind($this->outputStream->getStream());

        return $this->rtrimMultiline(stream_get_contents($this->outputStream->getStream()));
    }

    protected function getErrorConsoleStyle(): ErrorsConsoleStyle
    {
        return $this->errorConsoleStyle;
    }

    protected function getAnalysisResult(int $numFileErrors, int $numGenericErrors): AnalysisResult
    {
        if ($numFileErrors > 4 || $numFileErrors < 0 || $numGenericErrors > 2 || $numGenericErrors < 0) {
            throw new \Exception();
        }

        $fileErrors = array_slice([
            new Error('Foo', self::DIRECTORY_PATH . '/folder with unicode 😃/file name with "spaces" and unicode 😃.php', 4),
            new Error('Foo', self::DIRECTORY_PATH . '/foo.php', 1),
            new Error('Bar', self::DIRECTORY_PATH . '/foo.php', 5),
            new Error('Bar', self::DIRECTORY_PATH . '/folder with unicode 😃/file name with "spaces" and unicode 😃.php', 2),
        ], 0, $numFileErrors);

        $genericErrors = array_slice([
            'first generic error',
            'second generic error',
        ], 0, $numGenericErrors);

        return new AnalysisResult(
            $fileErrors,
            $genericErrors,
            false,
            self::DIRECTORY_PATH
        );
    }

    private function rtrimMultiline(string $output): string
    {
        $result = array_map(function (string $line): string {
            return rtrim($line, " \r\n");
        }, explode("\n", $output));

        return implode("\n", $result);
    }
}
