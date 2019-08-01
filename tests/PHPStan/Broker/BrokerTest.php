<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Cache\Cache;
use PHPStan\File\FileHelper;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\Parser\Parser;
use PHPStan\PhpDoc\PhpDocStringResolver;
use PHPStan\Reflection\FunctionReflectionFactory;
use PHPStan\Reflection\SignatureMap\SignatureMapProvider;
use PHPStan\Type\FileTypeMapper;

class BrokerTest extends \PHPStan\Testing\TestCase
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	protected function setUp(): void
	{
		$phpDocStringResolver = self::getContainer()->getByType(PhpDocStringResolver::class);

		$workingDirectory = __DIR__;
		$relativePathHelper = new FuzzyRelativePathHelper($workingDirectory, DIRECTORY_SEPARATOR, []);
		$anonymousClassNameHelper = new AnonymousClassNameHelper(new FileHelper($workingDirectory), $relativePathHelper);

		$this->broker = new Broker(
			[],
			[],
			[],
			[],
			[],
			[],
			$this->createMock(FunctionReflectionFactory::class),
			new FileTypeMapper($this->getParser(), $phpDocStringResolver, $this->createMock(Cache::class), $anonymousClassNameHelper, new \PHPStan\PhpDoc\TypeNodeResolver([], self::getContainer())),
			self::getContainer()->getByType(SignatureMapProvider::class),
			self::getContainer()->getByType(\PhpParser\PrettyPrinter\Standard::class),
			$anonymousClassNameHelper,
			self::getContainer()->getByType(Parser::class),
			$relativePathHelper,
			[]
		);
	}

	public function testClassNotFound(): void
	{
		$this->expectException(\PHPStan\Broker\ClassNotFoundException::class);
		$this->expectExceptionMessage('NonexistentClass');
		$this->broker->getClass('NonexistentClass');
	}

	public function testFunctionNotFound(): void
	{
		$this->expectException(\PHPStan\Broker\FunctionNotFoundException::class);
		$this->expectExceptionMessage('Function nonexistentFunction not found while trying to analyse it - autoloading is probably not configured properly.');

		$scope = $this->createMock(Scope::class);
		$scope->method('getNamespace')
			->willReturn(null);
		$this->broker->getFunction(new Name('nonexistentFunction'), $scope);
	}

	public function testClassAutoloadingException(): void
	{
		$this->expectException(\PHPStan\Broker\ClassAutoloadingException::class);
		$this->expectExceptionMessage("ParseError (syntax error, unexpected '{') thrown while autoloading class NonexistentClass.");
		spl_autoload_register(static function (): void {
			require_once __DIR__ . '/../Analyser/data/parse-error.php';
		}, true, true);
		$this->broker->hasClass('NonexistentClass');
	}

}
