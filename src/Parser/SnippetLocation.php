<?php

namespace PHPStan\Parser;


class SnippetLocation
{
    private const SNIPPET_SIZE = 255;

    /** @var string */
    private $snippet;

    public function __construct(string $filePath, \PhpParser\Node $stmt)
    {
        $fileContents = file_get_contents($filePath);
        $fileLength = strlen($fileContents);
        $fileStart = (int)$stmt->getAttribute('startFilePos');
        $fileEnd = (int)$stmt->getAttribute('endFilePos');


        //get start position of snippet
        $phpDocComment = $stmt->getDocComment(); //если есть пхпдок - то показываем начиная от него
        $previewStart = $phpDocComment ? $phpDocComment->getFilePos() : $fileStart;
        $previewStart = (int)mb_strrpos(
                $fileContents,
                "\n",
                min($previewStart, $fileStart) - mb_strlen($fileContents)
            ) + 1;

        //get end position of snippet
        $selectionEnd = $fileEnd + 1;
        $previewEnd = $selectionEnd;
        if ($selectionEnd <= $fileLength) {
            $previewEnd = mb_strpos($fileContents, "\n", $selectionEnd);
            if ($previewEnd === false) {
                $previewEnd = $selectionEnd;
            }
        }

        $snippet = mb_substr($fileContents, $previewStart, $previewEnd - $previewStart);

        $this->snippet = $this->shortenIfExceeds($snippet);
    }

    public function getSnippet(): string
    {
        return $this->snippet;
    }

    private function shortenIfExceeds(string $snippet): string
    {
        if (mb_strlen($snippet) > self::SNIPPET_SIZE) {
            $lineBreak = mb_strpos($snippet, "\n", self::SNIPPET_SIZE);
            if ($lineBreak === false) {
                $lineBreak = strlen($snippet);
            }
            $snippet = mb_substr($snippet, 0, $lineBreak);
        }
        return $snippet;
    }
}