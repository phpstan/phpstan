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

}
