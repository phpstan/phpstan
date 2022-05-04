<?php

declare(strict_types=1);

namespace App\Security\Authorization;

enum Role : string
{
    case SUPERVISOR = 'ROLE_SUPERVISOR';
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';
}
