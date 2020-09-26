<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ContentPressSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:setup {--n} {--s}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will drop all tables and create the default dummy data (if the --seed option is provided).';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //#! New
        if ( $this->option( 'n' ) ) {
            $this->line( '>>> Dropping & creating database tables...' );
            Artisan::call( 'migrate:fresh' );
            $this->line( '== Done ==' );
        }

        //#! Seed
        if ( $this->option( 's' ) ) {
            $this->line( '>>> Inserting the default dummy data...' );
            Artisan::call( 'migrate --seed' );
            $this->line( '== Done ==' );
        }
        return 1;
    }
}
