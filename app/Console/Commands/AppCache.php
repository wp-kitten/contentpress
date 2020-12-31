<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AppCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will execute all available commands for clearing the application cache.';

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
        $this->line( '>>> Clearing all caches' );

        $this->line( '>>> Clearing cache' );
        Artisan::call( 'cache:clear' );
        $this->line( '== Done ==' );

        $this->line( '>>> Clearing route cache' );
        Artisan::call( 'route:clear' );
        $this->line( '== Done ==' );

        $this->line( '>>> Clearing config cache' );
        Artisan::call( 'config:clear' );
        $this->line( '== Done ==' );

        $this->line( '>>> Clearing view cache' );
        Artisan::call( 'view:clear' );
        $this->line( '== Done ==' );

        $this->line( '>>> Clearing compiled cache' );
        Artisan::call( 'clear-compiled' );
        $this->line( '== Done ==' );

        $this->line( '>>> Clearing internal cache' );
        app()->get( 'vp.cache' )->clear();
        $this->line( '== Done ==' );

        return 1;
    }
}
