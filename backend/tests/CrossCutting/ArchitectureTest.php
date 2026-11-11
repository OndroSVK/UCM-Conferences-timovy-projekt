<?php

// Architecture rules of backend.md 3 and 4.3, checked on the source code.

use Symfony\Component\Finder\Finder;

function sourceFiles(string $path): array
{
    $files = [];
    foreach ((new Finder)->files()->in(base_path($path))->name('*.php') as $file) {
        $files[str_replace('\\', '/', $file->getRelativePathname())] = $file->getContents();
    }

    return $files;
}

// 4.3: a status column is written only by Workflow\Actions\ChangeStatus.
it('writes status columns only in ChangeStatus', function () {
    $offenders = [];

    foreach (sourceFiles('app') as $path => $code) {
        if ($path === 'Modules/Workflow/Actions/ChangeStatus.php') {
            continue;
        }

        $patterns = [
            '/->\s*(payment_)?status\s*=(?!=)/',                       // $model->status = ...
            // ['status' => ...] in create/fill/update; enum casts ('status' => X::class) and
            // validation rules ('status' => [...]) are fine
            '/[\'"](payment_)?status[\'"]\s*=>\s*+(?![A-Z]\w*::class|\[)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $code)) {
                // Invoices have their own technical issue status, not a workflow status.
                if (str_starts_with($path, 'Modules/Invoicing/')) {
                    continue;
                }
                $offenders[] = $path;
            }
        }
    }

    expect(array_unique($offenders))->toBe([]);
});

it('keeps status columns out of mass assignment', function () {
    expect((new \App\Modules\Registration\Models\Registration)->getFillable())
        ->not->toContain('status')->not->toContain('payment_status')
        ->and((new \App\Modules\Submissions\Models\Submission)->getFillable())->not->toContain('status');
});

// 3.3 rule 6: Conferences and Auth depend only on Shared (relations to User excepted).
it('Conferences and Auth actions do not use other modules', function () {
    foreach (['Conferences', 'Auth'] as $module) {
        foreach (sourceFiles("app/Modules/{$module}/Actions") as $path => $code) {
            preg_match_all('/^use App\\\\Modules\\\\(\w+)\\\\/m', $code, $m);
            $foreign = array_diff(array_unique($m[1]), [$module]);
            expect($foreign)->toBe([], "{$module}/Actions/{$path}");
        }
    }
});

// 3.3 rule 5: Shared contains no module logic.
it('Shared uses no module except the Conference model for the scope relation', function () {
    foreach (sourceFiles('app/Shared') as $path => $code) {
        preg_match_all('/^use (App\\\\Modules\\\\[\w\\\\]+);/m', $code, $m);
        expect(array_diff($m[1], ['App\Modules\Conferences\Models\Conference']))->toBe([], $path);
    }
});

// BR2: every feature has a test — checked loosely by the feature name appearing in tests.
it('every Action is mentioned in a test', function () {
    $tests = implode("\n", sourceFiles('tests'));
    $missing = [];

    foreach (array_keys(sourceFiles('app/Modules')) as $path) {
        if (preg_match('#/Actions/(\w+)\.php$#', $path, $m) && ! str_contains($tests, $m[1])) {
            $missing[] = $path;
        }
    }

    expect($missing)->toBe([]);
});

// Every conference-bound table has a model with BelongsToConference.
it('every model with conference_id uses BelongsToConference', function () {
    foreach (sourceFiles('app/Modules') as $path => $code) {
        if (! str_contains($path, '/Models/') || ! str_contains($code, "'conference_id'")) {
            continue;
        }
        expect(str_contains($code, 'use BelongsToConference;'))->toBeTrue($path);
    }
});
