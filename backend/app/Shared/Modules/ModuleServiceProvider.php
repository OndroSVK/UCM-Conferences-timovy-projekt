<?php

namespace App\Shared\Modules;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Base provider of a module: migrations, views and event listeners (backend.md 3.3 rule 4).
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Event class => list of listener classes.
     *
     * @return array<class-string, list<class-string>>
     */
    protected function listeners(): array
    {
        return [];
    }

    /** Namespace for module views, e.g. "invoicing::pdf". Null when the module has no views. */
    protected function viewNamespace(): ?string
    {
        return null;
    }

    public function boot(): void
    {
        $directory = dirname((new ReflectionClass(static::class))->getFileName());

        if (is_dir($directory.'/Database/Migrations')) {
            $this->loadMigrationsFrom($directory.'/Database/Migrations');
        }

        if ($this->viewNamespace() !== null && is_dir($directory.'/Resources/views')) {
            $this->loadViewsFrom($directory.'/Resources/views', $this->viewNamespace());
        }

        foreach ($this->listeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
