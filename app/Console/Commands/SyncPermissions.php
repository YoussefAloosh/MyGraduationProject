<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SyncPermissions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions based on database tables';

    protected $ignoredTables = [
        'migrations',
        'password_reset_tokens',
        'failed_jobs',
        'personal_access_tokens',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'sessions',
    ];

    protected $actions = ['view', 'create', 'edit', 'delete'];



    /**
     * Execute the console command.
     */
    public function handle()
    {

        $tables = $this->getTables();

        foreach ($tables as $table) {
            foreach ($this->actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$table}.{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $this->info('Permissions synced!');
    }

    private function getTables(): array
    {
        $tables = DB::select('SHOW TABLES');

        // ناخد الـ key تلقائياً من أول record
        $firstRow = (array) $tables[0];
        $key = array_key_first($firstRow);

        return collect($tables)
            ->map(fn($t) => ((array) $t)[$key])
            ->filter(fn($t) => !in_array($t, $this->ignoredTables))
            ->values()
            ->toArray();
    }
}
