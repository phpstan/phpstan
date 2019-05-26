<?php declare(strict_types = 1);

namespace PHPStan\DependencyInjection;

use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

class ParametersSchemaExtension extends \Nette\DI\CompilerExtension
{

	public function getConfigSchema(): \Nette\Schema\Schema
	{
		return Expect::arrayOf(Expect::type(Statement::class))->min(1);
	}

	public function loadConfiguration(): void
	{
		$config = $this->config;
		$config['__parametersSchema'] = new Statement(Schema::class);
		$builder = $this->getContainerBuilder();
		$builder->parameters['__parametersSchema'] = $this->processArgument(
			new Statement('schema', [
				new Statement('structure', [$config]),
			])
		);
	}

	/**
	 * @param Statement[] $statements
	 * @return \Nette\Schema\Schema
	 */
	private function processSchema(array $statements): Schema
	{
		if (count($statements) === 0) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$parameterSchema = null;
		foreach ($statements as $statement) {
			$processedArguments = array_map(function ($argument) {
				return $this->processArgument($argument);
			}, $statement->arguments);
			if ($parameterSchema === null) {
				/** @var \Nette\Schema\Elements\Type|\Nette\Schema\Elements\AnyOf|\Nette\Schema\Elements\Structure $parameterSchema */
				$parameterSchema = Expect::{$statement->getEntity()}(...$processedArguments);
			} else {
				$parameterSchema->{$statement->getEntity()}(...$processedArguments);
			}
		}

		$parameterSchema->required();

		return $parameterSchema;
	}

	/**
	 * @param mixed $argument
	 * @return mixed
	 */
	private function processArgument($argument)
	{
		if ($argument instanceof Statement) {
			if ($argument->entity === 'schema') {
				$arguments = [];
				foreach ($argument->arguments as $schemaArgument) {
					if (!$schemaArgument instanceof Statement) {
						throw new \PHPStan\ShouldNotHappenException('schema() should contain another statement().');
					}

					$arguments[] = $schemaArgument;
				}

				if (count($arguments) === 0) {
					throw new \PHPStan\ShouldNotHappenException('schema() should have at least one argument.');
				}

				return $this->processSchema($arguments);
			}

			return $this->processSchema([$argument]);
		} elseif (is_array($argument)) {
			$processedArray = [];
			foreach ($argument as $key => $val) {
				$processedArray[$key] = $this->processArgument($val);
			}

			return $processedArray;
		}

		return $argument;
	}

}
