<?php

use App\Modules\Auth\Models\User;
use App\Modules\Conferences\Models\Conference;
use App\Shared\Auth\AccessControl;

// Console commands for a real installation: conferences:create-user, conferences:create,
// conferences:db-check. They only call Actions (CreateStaffAccount, CreateConference).

it('creates the first superadmin without printing the password', function () {
    $this->artisan('conferences:create-user', [
        'email' => 'root@example.test', 'name' => 'Root Admin', '--superadmin' => true, '--password' => 'very-secret-1',
    ])->doesntExpectOutputToContain('very-secret-1')->assertSuccessful();

    $user = User::query()->where('email', 'root@example.test')->firstOrFail();
    expect(app(AccessControl::class)->isSuperadmin($user))->toBeTrue();
});

it('asks for the password interactively when it is not given', function () {
    $this->artisan('conferences:create-user', ['email' => 'staff@example.test', 'name' => 'Staff'])
        ->expectsQuestion('Password (min. 8 characters)', 'typed-secret-1')
        ->assertSuccessful();

    expect(User::query()->where('email', 'staff@example.test')->exists())->toBeTrue();
});

it('reports validation errors and creates nothing', function () {
    $this->artisan('conferences:create-user', ['email' => 'not-an-email', 'name' => 'X', '--password' => 'very-secret-1'])
        ->assertFailed();

    expect(User::query()->count())->toBe(0);
});

it('creates a conference from the example JSON file', function () {
    $this->artisan('conferences:create', ['file' => database_path('conference.example.json')])->assertSuccessful();

    expect(Conference::query()->where('subdomain', 'ans2027')->exists())->toBeTrue();
});

it('fails on a missing or broken conference file', function () {
    $this->artisan('conferences:create', ['file' => base_path('no-such-file.json')])->assertFailed();

    $broken = tempnam(sys_get_temp_dir(), 'conf');
    file_put_contents($broken, '{ not json');
    $this->artisan('conferences:create', ['file' => $broken])->assertFailed();
    unlink($broken);
});

it('db-check fails on a non-MariaDB driver unless explicitly allowed', function () {
    if (in_array(DB::getDriverName(), ['mariadb', 'mysql'], true)) {
        $this->artisan('conferences:db-check', ['--skip-locks' => true])->assertSuccessful();

        return;
    }

    $this->artisan('conferences:db-check')->assertFailed();
    $this->artisan('conferences:db-check', ['--allow-sqlite' => true])
        ->expectsOutputToContain('Database check passed')
        ->assertSuccessful();
});
