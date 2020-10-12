<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ContentPressComposer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:composer {--u} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tries to execute: --u for composer update, --d for composer dumpautoload';

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
     * @return int
     */
    public function handle()
    {
        try {
            if ( $this->option( 'u' ) ) {
                $this->line( '>>> Running: composer update' );
                $process = new Process( [ 'composer update' ] );
                $process->run();
                $this->line( '== Done ==' );
            }
            if ( $this->option( 'd' ) ) {
                $this->line( '>>> Running: composer dumpautoload' );
                $process = new Process( [ 'composer dumpautoload' ] );
                $process->run();
                $this->line( '== Done ==' );
            }
        }
        catch ( \Exception $e ) {
            $this->line( __( 'a.An error occurred :error', [ 'error' => $e->getMessage() ] ) );
        }
        return 1;
    }
}
