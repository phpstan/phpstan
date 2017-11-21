<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;

class PhpDocStringResolver
{

	/** @var Lexer */
	private $phpDocLexer;

	/** @var PhpDocParser */
	private $phpDocParser;

	/** @var PhpDocNodeResolver */
	private $phpDocNodeResolver;

	public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser, PhpDocNodeResolver $phpDocNodeResolver)
	{
		$this->phpDocNodeResolver = $phpDocNodeResolver;
		$this->phpDocLexer = $phpDocLexer;
		$this->phpDocParser = $phpDocParser;
	}

	public function resolve(string $phpDocString, NameScope $nameScope): ResolvedPhpDocBlock
	{
		$tokens = new TokenIterator($this->phpDocLexer->tokenize($phpDocString));
		$phpDocNode = $this->phpDocParser->parse($tokens);
		$tokens->consumeTokenType(Lexer::TOKEN_END);

		return $this->phpDocNodeResolver->resolve($phpDocNode, $nameScope);
	}

}
