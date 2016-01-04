<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Broker\Broker;

interface BrokerAwareClassReflectionExtension
{

	public function setBroker(Broker $broker);

}
