<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\Command\AnalysisResult;
use Symfony\Component\Console\Formatter\OutputFormatter;

class JunitErrorFormatter implements ErrorFormatter
{

	public function formatErrors(
		AnalysisResult $analysisResult,
		\Symfony\Component\Console\Style\OutputStyle $style
	): int
	{
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $testsuites = $dom->appendChild($dom->createElement('testsuites'));
        /** @var \DomElement $testsuite */
        $testsuite = $testsuites->appendChild($dom->createElement('testsuite'));
        $testsuite->setAttribute('name', 'PHPStan');

        $returnCode = 1;

		if (!$analysisResult->hasErrors()) {
            $testcase = $dom->createElement('testcase');
            $testsuite->appendChild($testcase);
            $testsuite->setAttribute('tests', '1');
            $testsuite->setAttribute('failures', '0');

            $returnCode = 0;
		} else {
            $currentDirectory = $analysisResult->getCurrentDirectory();
            $cropFilename = function (string $filename) use ($currentDirectory): string {
                if ($currentDirectory !== '' && strpos($filename, $currentDirectory) === 0) {
                    return substr($filename, strlen($currentDirectory) + 1);
                }

                return $filename;
            };

            /** @var \PHPStan\Analyser\Error[][] $fileErrors */
            $fileErrors = [];
            foreach ($analysisResult->getFileSpecificErrors() as $fileSpecificError) {
                if (!isset($fileErrors[$fileSpecificError->getFile()])) {
                    $fileErrors[$fileSpecificError->getFile()] = [];
                }

                $fileErrors[$fileSpecificError->getFile()][] = $fileSpecificError;
            }

            foreach ($fileErrors as $file => $errors) {

                $testcase = $dom->createElement('testcase');
                $testcase->setAttribute('file', $cropFilename($file));
                $testcase->setAttribute('failures', (string) count($errors));
                $testcase->setAttribute('errors', (string) count($errors));
                $testcase->setAttribute('tests', (string) count($errors));

                foreach ($errors as $error) {

                    $failure = $dom->createElement('failure', sprintf('On line %d', $error->getLine()));
                    $failure->setAttribute('type', 'error');
                    $failure->setAttribute('message', $error->getMessage());
                    $testcase->appendChild($failure);
                }

                $testsuite->appendChild($testcase);
            }
        }

        $dom->formatOutput = true;

        $style->write($style->isDecorated() ? OutputFormatter::escape($dom->saveXML()) : $dom->saveXML());

		return $returnCode;
	}

}
