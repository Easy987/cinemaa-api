<?php

namespace App\Models;

use App\Traits\UUIDTrait;
use \Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    use UUIDTrait;
}
