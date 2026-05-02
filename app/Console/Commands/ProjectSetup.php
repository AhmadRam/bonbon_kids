<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProjectSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically run migrations, and seed the database if it is the first run.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Project Setup...');

        // Check if database has been migrated before
        $isFresh = false;
        
        try {
            if (!Schema::hasTable('migrations') || DB::table('migrations')->count() === 0) {
                $isFresh = true;
            }
        } catch (\Exception $e) {
            // If we can't even connect or query, assume it's fresh (e.g. database just created)
            $isFresh = true;
        }

        if ($isFresh) {
            $this->info('Database appears to be empty (First run detected).');
            $this->info('Running migrate:fresh --seed...');
            
            Artisan::call('migrate:fresh', ['--seed' => true], $this->output);
            
            $this->info('Database has been migrated and seeded successfully.');
        } else {
            $this->info('Database already contains data (Not the first run).');
            $this->info('Running migrate to apply any new changes...');
            
            Artisan::call('migrate', ['--force' => true], $this->output);
            
            $this->info('Migrations applied successfully.');
        }

        return 0;
    }
}
