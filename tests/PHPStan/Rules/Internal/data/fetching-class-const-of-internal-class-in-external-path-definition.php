<?php

namespace FetchingClassConstOfInternalClassInExternalPath;

class Foo
{

	public const FOO = 'FOO';

	/**
	 * @internal
	 */
	public const INTERNAL_FOO = 'FOO';

}

/**
 * @internal
 */
class InternalFoo
{

	public const FOO = 'FOO';

	/**
	 * @internal
	 */
	public const INTERNAL_FOO = 'FOO';

}
