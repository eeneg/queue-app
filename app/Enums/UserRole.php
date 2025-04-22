<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AGENTS = 'agents';
    case FRONTDESK = 'frontdesk';
}
