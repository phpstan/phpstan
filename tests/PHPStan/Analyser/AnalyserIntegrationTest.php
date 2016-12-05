<?php declare(strict_types = 1);

namespace PHPStan\Analyser;

class AnalyserIntegrationTest extends \PHPStan\TestCase
{

	public function testUndefinedVariableFromAssignErrorHasLine()
	{
		$file = __DIR__ . '/data/undefined-variable-assign.php';
		$analyser = $this->getContainer()->getByType(Analyser::class);
		$errors = $analyser->analyse([$file]);
		$this->assertCount(1, $errors);
		$error = $errors[0];
		$this->assertSame('Undefined variable: $bar', $error->getMessage());
		$this->assertSame($file, $error->getFile());
		$this->assertSame(3, $error->getLine());
	}

	public function testMissingPropertyAndMethod()
	{
		$file = __DIR__ . '/../../notAutoloaded/Foo.php';
		$analyser = $this->getContainer()->getByType(Analyser::class);
		$errors = $analyser->analyse([$file]);
		$this->assertCount(1, $errors);
		$error = $errors[0];
		$this->assertSame('Property $fooProperty was not found in reflection of class PHPStan\Tests\Foo - probably the wrong version of class is autoloaded.', $error->getMessage());
		$this->assertSame($file, $error->getFile());
		$this->assertNull($error->getLine());
	}

	public function testErrorAboutMisconfiguredAutoloader()
	{
		$file = __DIR__ . '/../../notAutoloaded/Bar.php';
		$analyser = $this->getContainer()->getByType(Analyser::class);
		$errors = $analyser->analyse([$file]);
		$this->assertCount(1, $errors);
		$error = $errors[0];
		$this->assertSame('Class PHPStan\Tests\Bar was not found while trying to analyse it - autoloading is not probably configured properly.', $error->getMessage());
		$this->assertSame($file, $error->getFile());
		$this->assertNull($error->getLine());
	}

}
