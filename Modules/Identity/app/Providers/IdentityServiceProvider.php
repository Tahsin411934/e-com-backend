<?php

namespace Modules\Identity\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class IdentityServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Identity';

    protected string $nameLower = 'identity';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
