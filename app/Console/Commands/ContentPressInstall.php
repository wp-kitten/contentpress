<?php

namespace App\Console\Commands;

use App\Helpers\Marketplace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ContentPressInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:install {--n} {--s} {--t}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will install ContentPress. --n will reinstall tables, --s will run the seeders, --t will download and install the default theme';

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
        //#! New
        if ( $this->option( 'n' ) ) {
            $this->line( '>>> Dropping & creating the database tables...' );

            Artisan::call( 'migrate:fresh' );
            $this->line( '== Done ==' );

            $this->line( '>>> Deleting uploads...' );
            try {
                $uploadsDirPath = public_path( 'uploads' );
                if ( File::isDirectory( $uploadsDirPath ) ) {
                    File::deleteDirectory( $uploadsDirPath );
                }
                File::makeDirectory( $uploadsDirPath );
                $this->line( '>>> DONE...' );
            }
            catch ( \Exception $e ) {
                $this->line( '== An error occurred: ' . $e->getMessage() . ' ==' );
            }
        }

        //#! Seed
        if ( $this->option( 's' ) ) {
            $this->line( '>>> Running seeders...' );
            Artisan::call( 'db:seed' );
            $this->line( '== Done ==' );
        }

        //#! Install and activate the default theme
        if ( $this->option( 't' ) ) {
            try {
                $this->line( '>>> Downloading and installing the default theme...' );
                ( new Marketplace() )->installTheme( 'contentpress-default-theme', '0.1' );
                $this->line( '>>> Activating the default theme...' );
                do_action( 'contentpress/switch_theme', 'contentpress-default-theme', '' );
                $this->line( '== Done ==' );
            }
            catch ( \Exception $e ) {
                $this->line( '== An error occurred. Run this command again: php artisan cp:install --t ==' );
                return 0;
            }
        }
        return 1;
    }
}
