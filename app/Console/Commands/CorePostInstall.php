<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CorePostInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:post-install {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes various commands after a core update.';

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
        if ( $this->option( 'd' ) ) {
            $this->line( '>>> Deleting uploads...' );
            try {
                $uploadsDirPath = public_path( 'uploads' );
                if ( File::isDirectory( $uploadsDirPath ) ) {
                    File::deleteDirectory( $uploadsDirPath );
                }
                File::makeDirectory( $uploadsDirPath );
                $this->line( '== Done ==' );
            }
            catch ( \Exception $e ) {
                $this->line( '== An error occurred: ' . $e->getMessage() . ' ==' );
            }
        }

        //#! Clear cache
        $this->call( 'vp:cache' );
        app()->get( 'vp.cache' )->clear();

        //#! Try to run composer dumpautoload
        return $this->call( 'vp:composer', [
            '--d' => true,
        ] );
    }
}
