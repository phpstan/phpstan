<?php

namespace CheckInternalFunctionCall;

\CheckInternalFunctionCallInInternalPath\foo();
\CheckInternalFunctionCallInInternalPath\internal_foo();

\CheckInternalFunctionCallInExternalPath\foo();
\CheckInternalFunctionCallInExternalPath\internal_foo();
