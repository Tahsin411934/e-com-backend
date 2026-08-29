<?php

namespace Modules\History\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\History\Observers\HistoryObserver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class HistoryServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'History';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'history';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        foreach (config('history.models', []) as $modelClass) {
            if (class_exists($modelClass)) {
                $modelClass::observe(HistoryObserver::class);
            }
        }
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
