<?php

// Builds the playground's PHPStan stub shells: downloads sources from
// phpstan-src, strips the function/method bodies with PHPStan's CleaningVisitor
// (inlined below so this script depends only on nikic/php-parser, not on any
// phpstan internals), and writes them into src/js/phpantom/stubs/ mirroring the
// phpstan-src layout. The playground worker loads that directory recursively, so
// adding a path to SOURCES below is the only change needed to ship another stub.
//
// Run via `npm run build:stubs` (PHP + Composer required; both are present in
// the website CI build job).

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

require __DIR__ . '/vendor/autoload.php';

/**
 * Copied verbatim from phpstan-src src/Parser/CleaningVisitor.php, with
 * ParametersAcceptor::VARIADIC_FUNCTIONS inlined and the #[\Override] attribute
 * dropped, so it carries no dependency beyond nikic/php-parser. Empties
 * function/method/closure bodies while keeping signatures (and variadics/yields).
 */
final class CleaningVisitor extends NodeVisitorAbstract
{

	private const VARIADIC_FUNCTIONS = ['func_get_args', 'func_get_arg', 'func_num_args'];

	private NodeFinder $nodeFinder;

	public function __construct()
	{
		$this->nodeFinder = new NodeFinder();
	}

	public function enterNode(Node $node): ?Node
	{
		if ($node instanceof Node\Stmt\Function_) {
			$node->stmts = $this->keepVariadicsAndYields($node->stmts, null);
			return $node;
		}

		if ($node instanceof Node\Stmt\ClassMethod && $node->stmts !== null) {
			$node->stmts = $this->keepVariadicsAndYields($node->stmts, null);
			return $node;
		}

		if ($node instanceof Node\Expr\Closure) {
			$node->stmts = $this->keepVariadicsAndYields($node->stmts, null);
			return $node;
		}

		if ($node instanceof Node\PropertyHook && is_array($node->body)) {
			$propertyName = $node->getAttribute('propertyName');
			if ($propertyName !== null) {
				$node->body = $this->keepVariadicsAndYields($node->body, $propertyName);
				return $node;
			}
		}

		return null;
	}

	/**
	 * @param list<Node\Stmt|Node\Expr> $stmts
	 * @return list<Node\Stmt>
	 */
	private function keepVariadicsAndYields(array $stmts, ?string $hookedPropertyName): array
	{
		$results = $this->nodeFinder->find($stmts, static function (Node $node) use ($hookedPropertyName): bool {
			if ($node instanceof Node\Expr\YieldFrom || $node instanceof Node\Expr\Yield_) {
				return true;
			}
			if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
				return in_array($node->name->toLowerString(), self::VARIADIC_FUNCTIONS, true);
			}

			if ($node instanceof Node\Expr\Closure || $node instanceof Node\Expr\ArrowFunction) {
				return true;
			}

			if ($hookedPropertyName !== null) {
				if (
					$node instanceof Node\Expr\PropertyFetch
					&& $node->var instanceof Node\Expr\Variable
					&& $node->var->name === 'this'
					&& $node->name instanceof Node\Identifier
					&& $node->name->toString() === $hookedPropertyName
				) {
					return true;
				}
			}

			return false;
		});
		$newStmts = [];
		foreach ($results as $result) {
			if (
				$result instanceof Node\Expr\Yield_
				|| $result instanceof Node\Expr\YieldFrom
				|| $result instanceof Node\Expr\Closure
				|| $result instanceof Node\Expr\ArrowFunction
				|| $result instanceof Node\Expr\PropertyFetch
			) {
				$newStmts[] = new Node\Stmt\Expression($result);
				continue;
			}
			if (!$result instanceof Node\Expr\FuncCall) {
				continue;
			}

			$newStmts[] = new Node\Stmt\Expression(new Node\Expr\FuncCall(new Node\Name\FullyQualified('func_get_args')));
		}

		return $newStmts;
	}

}

$ref = getenv('PHPSTAN_SRC_REF') ?: '2.2.x';
$base = "https://raw.githubusercontent.com/phpstan/phpstan-src/$ref";
$outDir = __DIR__ . '/../../src/js/phpantom/stubs';

// Paths within phpstan-src to ship as cleaned shells. The output mirrors these.
$sources = [
	'src/Testing/functions.php',
	'src/TrinaryLogic.php',
	'src/debugScope.php',
	'src/dumpType.php',
];

$download = static function (string $url): string {
	$contents = @file_get_contents($url);
	if ($contents === false) {
		fwrite(STDERR, "error: failed to download $url\n");
		exit(1);
	}
	return $contents;
};

$rrmdir = static function (string $dir) use (&$rrmdir): void {
	if (!is_dir($dir)) {
		return;
	}
	foreach (scandir($dir) as $entry) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}
		$path = "$dir/$entry";
		is_dir($path) ? $rrmdir($path) : unlink($path);
	}
	rmdir($dir);
};

$rrmdir($outDir);

$parser = (new ParserFactory())->createForNewestSupportedVersion();
$printer = new Standard();

echo "==> building PHPStan stub shells (ref: $ref)\n";
foreach ($sources as $relPath) {
	$code = $download("$base/$relPath");

	$oldStmts = $parser->parse($code);
	$oldTokens = $parser->getTokens();
	$newStmts = (new NodeTraverser(new CloningVisitor()))->traverse($oldStmts);
	$newStmts = (new NodeTraverser(new CleaningVisitor()))->traverse($newStmts);
	$cleaned = $printer->printFormatPreserving($newStmts, $oldStmts, $oldTokens) . "\n";

	$target = "$outDir/$relPath";
	$dir = dirname($target);
	if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
		fwrite(STDERR, "error: could not create $dir\n");
		exit(1);
	}
	file_put_contents($target, $cleaned);
	printf("    %s (%d bytes)\n", $relPath, strlen($cleaned));
}
