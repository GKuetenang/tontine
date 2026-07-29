<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\TypeScriptTransformerServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
    PermissionServiceProvider::class,
];
