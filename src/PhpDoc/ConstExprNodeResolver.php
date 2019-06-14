<?php declare(strict_types = 1);

namespace PHPStan\PhpDoc;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprArrayNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class ConstExprNodeResolver
{

	public function resolve(ConstExprNode $node): Type
	{
		if ($node instanceof ConstExprArrayNode) {
			return $this->resolveArrayNode($node);
		}

		if ($node instanceof ConstExprFalseNode) {
			return ConstantTypeHelper::getTypeFromValue(false);
		}

		if ($node instanceof ConstExprTrueNode) {
			return ConstantTypeHelper::getTypeFromValue(true);
		}

		if ($node instanceof ConstExprFloatNode) {
			return ConstantTypeHelper::getTypeFromValue((float) $node->value);
		}

		if ($node instanceof ConstExprIntegerNode) {
			return ConstantTypeHelper::getTypeFromValue((int) $node->value);
		}

		if ($node instanceof ConstExprNullNode) {
			return ConstantTypeHelper::getTypeFromValue(null);
		}

		if ($node instanceof ConstExprStringNode) {
			return ConstantTypeHelper::getTypeFromValue($node->value);
		}

		return new MixedType();
	}

	private function resolveArrayNode(ConstExprArrayNode $node): ArrayType
	{
		$arrayBuilder = ConstantArrayTypeBuilder::createEmpty();
		foreach ($node->items as $item) {
			if ($item->key === null) {
				$key = null;
			} else {
				$key = $this->resolve($item->key);
			}
			$arrayBuilder->setOffsetValueType($key, $this->resolve($item->value));
		}

		return $arrayBuilder->getArray();
	}

}
