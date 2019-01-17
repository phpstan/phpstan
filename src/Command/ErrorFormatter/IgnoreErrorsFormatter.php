<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\File\RelativePathHelper;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Yaml\Yaml;

class IgnoreErrorFormatter implements ErrorFormatter
{
    /** @var RelativePathHelper */
    private $relativePathHelper;

    public function __construct(RelativePathHelper $relativePathHelper)
    {
        $this->relativePathHelper = $relativePathHelper;
    }

    public function formatErrors(
        AnalysisResult $analysisResult,
        OutputStyle $style
    ): int
    {
        if (!$analysisResult->hasErrors()) {
            return 0;
        }

        $errors = [];

        foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
            $errors[$fileSpecificError->getFile()][] = $fileSpecificError->getMessage();
        }

        foreach ($errors as $filename => $fileErrors) {
            $fileErrors = array_unique($fileErrors);

            foreach ($fileErrors as $message) {
                $style->writeln($this->formatError($filename, $message)));
            }
        }

        return 1;
    }

    public function formatError(string $filename, string $message): string
    {
        return sprintf(
            "        -\n            message: %s\n            path: %s",
            Yaml::dump('%currentWorkingDirectory%/src/' . $this->relativePathHelper->getRelativePath($filename)),
            Yaml::dump('~' . preg_quote(substr($message, 0, -1), '~') . '~')
        );
    }

}
