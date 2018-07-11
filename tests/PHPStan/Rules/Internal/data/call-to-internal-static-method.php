<?php

namespace CheckInternalStaticMethodCall;

\CheckInternalStaticMethodCallInInternalPath\Foo::foo();
\CheckInternalStaticMethodCallInInternalPath\Foo::internalFoo();
\CheckInternalStaticMethodCallInInternalPath\Bar::internalFoo();
\CheckInternalStaticMethodCallInInternalPath\Bar::internalFoo2();
\CheckInternalStaticMethodCallInInternalPath\InternalBar::foo();
\CheckInternalStaticMethodCallInInternalPath\InternalBar::internalFoo();
\CheckInternalStaticMethodCallInInternalPath\InternalBar::internalFoo2();

\CheckInternalStaticMethodCallInExternalPath\Foo::foo();
\CheckInternalStaticMethodCallInExternalPath\Foo::internalFoo();
\CheckInternalStaticMethodCallInExternalPath\Bar::internalFoo();
\CheckInternalStaticMethodCallInExternalPath\Bar::internalFoo2();
\CheckInternalStaticMethodCallInExternalPath\InternalBar::foo();
\CheckInternalStaticMethodCallInExternalPath\InternalBar::internalFoo();
\CheckInternalStaticMethodCallInExternalPath\InternalBar::internalFoo2();
