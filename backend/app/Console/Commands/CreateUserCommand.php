<?php

namespace App\Console\Commands;

use App\Modules\Auth\Actions\CreateStaffAccount;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/**
 * First superadmin and staff accounts for a real installation (the seeder creates
 * only fictional accounts). The password is never printed or logged.
 */
final class CreateUserCommand extends Command
{
    protected $signature = 'conferences:create-user
        {email : Login e-mail}
        {name : Display name}
        {--superadmin : Give the account the global superadmin role}
        {--password= : Password; asked interactively when omitted}';

    protected $description = 'Create a staff or superadmin account';

    public function handle(CreateStaffAccount $create): int
    {
        $password = $this->option('password') ?: ($this->input->isInteractive() ? $this->secret('Password (min. 8 characters)') : null);

        if (! $password) {
            $this->error('No password given. Use --password= or run the command interactively.');

            return self::FAILURE;
        }

        try {
            $user = $create->handle([
                'email' => $this->argument('email'),
                'name' => $this->argument('name'),
                'password' => $password,
            ], superadmin: (bool) $this->option('superadmin'));
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Created %s account #%d for %s.',
            $this->option('superadmin') ? 'superadmin' : 'staff',
            $user->id,
            $user->email,
        ));

        if (! $this->option('superadmin')) {
            $this->line('Assign it to a conference on the Team screen (superadmin).');
        }

        return self::SUCCESS;
    }
}
