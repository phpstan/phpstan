<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;

class StringAlwaysAcceptingObjectWithToStringType extends StringType
{

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof TypeWithClassName) {
			$broker = Broker::getInstance();
			if (!$broker->hasClass($type->getClassName())) {
				return TrinaryLogic::createNo();
			}

			$typeClass = $broker->getClass($type->getClassName());
			return TrinaryLogic::createFromBoolean(
				$typeClass->hasNativeMethod('__toString')
			);
		}

		return parent::accepts($type, $strictTypes);
	}

}
