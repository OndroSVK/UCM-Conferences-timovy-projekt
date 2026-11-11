<?php

namespace App\Shared\Modules;

/**
 * Enabled modules (config/modules.php, backend.md 3.3 rule 4).
 *
 * Optional modules are also checked at runtime (listeners, readers), so that a
 * disabled module never breaks the others — mandatory for Invoicing (O32).
 */
final class Modules
{
    public static function enabled(string $module): bool
    {
        return (bool) config("modules.enabled.{$module}", false);
    }

    /**
     * @return list<class-string>
     */
    public static function providers(): array
    {
        $providers = [];

        foreach (config('modules.enabled', []) as $module => $enabled) {
            if ($enabled) {
                $providers[] = "App\\Modules\\{$module}\\{$module}ServiceProvider";
            }
        }

        return $providers;
    }
}
