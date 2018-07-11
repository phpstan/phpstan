<?php declare(strict_types = 1);

namespace PHPStan\Rules\Internal;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\Php\PhpFunctionReflection;

class CallToInternalFunctionRule implements \PHPStan\Rules\Rule
{

	/** @var Broker */
	private $broker;

	/** @var InternalScopeHelper */
	private $internalScopeHelper;

	public function __construct(Broker $broker, InternalScopeHelper $internalScopeHelper)
	{
		$this->broker = $broker;
		$this->internalScopeHelper = $internalScopeHelper;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		try {
			$function = $this->broker->getFunction($node->name, $scope);
		} catch (\PHPStan\Broker\FunctionNotFoundException $e) {
			// Other rules will notify if the function is not found
			return [];
		}

		if (!$function instanceof PhpFunctionReflection) {
			return [];
		}

		if (!$function->isInternal()) {
			return [];
		}

		$functionFile = $function->getFileName();
		if ($functionFile === false) {
			return [];
		}

		if ($this->internalScopeHelper->isFileInInternalPaths($functionFile)) {
			return [];
		}

		return [sprintf(
			'Call to internal function %s().',
			$function->getName()
		)];
	}

}
