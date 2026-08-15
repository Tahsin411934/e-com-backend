<?php

namespace Modules\Reports\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ReportsServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Reports';

    protected string $nameLower = 'reports';

    protected array $providers = [
        RouteServiceProvider::class,
    ];
}
