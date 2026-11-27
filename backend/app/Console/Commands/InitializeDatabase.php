<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InitializeDatabase extends Command
{
    protected $signature = 'ans:init';

    protected $description = 'Inicializácia databázy ANS';

    public function handle()
    {
        $sqlFile = database_path('database/initDB.sql');

        if (!File::exists($sqlFile)) {
            $this->error('SQL súbor neexistuje!');
            return Command::FAILURE;
        }

        $sql = File::get($sqlFile);

        DB::unprepared($sql);

        $this->info('Databáza ANS bola inicializovaná.');

        return Command::SUCCESS;
    }
}
