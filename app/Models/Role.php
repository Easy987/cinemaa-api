<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use \Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use UUIDTrait;
}
