<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\InaccessibleMethod;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ClosureType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class CallCallablesRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionCallParametersCheck */
	private $check;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $reportMaybes;

	public function __construct(
		FunctionCallParametersCheck $check,
		RuleLevelHelper $ruleLevelHelper,
		bool $reportMaybes
	)
	{
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		Scope $scope
	): array
	{
		if (!$node->name instanceof \PhpParser\Node\Expr) {
			return [];
		}

		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->name,
			'Invoking callable on an unknown class %s.',
			static function (Type $type): bool {
				return $type->isCallable()->yes();
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}

		$isCallable = $type->isCallable();
		if ($isCallable->no()) {
			return [
				sprintf('Trying to invoke %s but it\'s not a callable.', $type->describe(VerbosityLevel::value())),
			];
		}
		if ($this->reportMaybes && $isCallable->maybe()) {
			return [
				sprintf('Trying to invoke %s but it might not be a callable.', $type->describe(VerbosityLevel::value())),
			];
		}

		$parametersAcceptors = $type->getCallableParametersAcceptors($scope);
		$messages = [];

		if (
			count($parametersAcceptors) === 1
			&& $parametersAcceptors[0] instanceof InaccessibleMethod
		) {
			$method = $parametersAcceptors[0]->getMethod();
			$messages[] = sprintf(
				'Call to %s method %s() of class %s.',
				$method->isPrivate() ? 'private' : 'protected',
				$method->getName(),
				$method->getDeclaringClass()->getDisplayName()
			);
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$node->args,
			$parametersAcceptors
		);

		if ($type instanceof ClosureType) {
			$callableDescription = 'closure';
		} else {
			$callableDescription = sprintf('callable %s', $type->describe(VerbosityLevel::value()));
		}

		return array_merge(
			$messages,
			$this->check->check(
				$parametersAcceptor,
				$scope,
				$node,
				[
					ucfirst($callableDescription) . ' invoked with %d parameter, %d required.',
					ucfirst($callableDescription) . ' invoked with %d parameters, %d required.',
					ucfirst($callableDescription) . ' invoked with %d parameter, at least %d required.',
					ucfirst($callableDescription) . ' invoked with %d parameters, at least %d required.',
					ucfirst($callableDescription) . ' invoked with %d parameter, %d-%d required.',
					ucfirst($callableDescription) . ' invoked with %d parameters, %d-%d required.',
					'Parameter #%d %s of ' . $callableDescription . ' expects %s, %s given.',
					'Result of ' . $callableDescription . ' (void) is used.',
					'Parameter #%d %s of ' . $callableDescription . ' is passed by reference, so it expects variables only.',
				]
			)
		);
	}

}
