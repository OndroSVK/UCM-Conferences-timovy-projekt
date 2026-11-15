<?php

namespace App\Console\Commands;

use App\Modules\Conferences\Actions\CreateConference;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/**
 * Creates a conference from a JSON definition (database/conference.example.json).
 */
final class CreateConferenceCommand extends Command
{
    protected $signature = 'conferences:create {file : Path to the JSON definition}';

    protected $description = 'Create a conference with categories, prices and option lists from a JSON file';

    public function handle(CreateConference $create): int
    {
        $path = (string) $this->argument('file');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $definition = json_decode((string) file_get_contents($path), true);

        if (! is_array($definition)) {
            $this->error('The file is not valid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }

        try {
            $conference = $create->handle($definition);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->info("Created conference #{$conference->id} \"{$conference->name}\" on subdomain {$conference->subdomain}.");

        return self::SUCCESS;
    }
}
