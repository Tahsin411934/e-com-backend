<?php

namespace Modules\Storefront\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class StorefrontServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Storefront';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'storefront';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
