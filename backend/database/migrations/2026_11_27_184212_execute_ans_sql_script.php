
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    public function up(): void
    {
        $sqlFile = database_path('sql/initDB.sql');

        if (!File::exists($sqlFile)) {
            throw new RuntimeException(
                "SQL súbor neexistuje: {$sqlFile}"
            );
        }

        $sql = File::get($sqlFile);

        // Odstránenie UTF-8 BOM
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

        // Odstránenie Windows BOM, ak sa objaví ako znak
        $sql = preg_replace('/^\x{FEFF}/u', '', $sql);

        DB::unprepared($sql);
    }

    public function down(): void
    {
        // SQL skript sa automaticky nevracia späť.
        // Prípadný rollback treba definovať samostatne.
    }
};
