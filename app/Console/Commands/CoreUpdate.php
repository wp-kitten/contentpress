<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class CoreUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:update {--v=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will attempt to update the ValPress core to the specified version.';

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
        $this->line( '>>> Attempting to update ValPress' );

        $version = $this->option( 'v' );
        if(! $version){
            $this->line( '>>> Please specify the version to update to' );
            return 0;
        }

        //#! Get archive from api server
        $response = Http::get( path_combine( VALPRESS_API_URL, 'get_update/core', $version ) );

        if ( empty( $response ) ) {
            $this->line( '>>> There was no response from the api server.' );
            return 0;
        }
        elseif ( $response instanceof Response ) {
            $response = $response->body();
        }

        //#! Download content locally
        try {
            $saveDirPath = public_path( 'uploads/tmp' );
            if ( !File::isDirectory( $saveDirPath ) ) {
                File::makeDirectory( $saveDirPath, 775, true );
            }
            $fileSavePath = path_combine( $saveDirPath, 'valpress.zip' );
            if ( !File::put( $fileSavePath, $response ) ) {
                $this->line( '>>> An error occurred when trying to create the local download file. Check for permissions.' );
                return 0;
            }
        }
        catch ( \Exception $e ) {
            $this->line( '>>> '.$e->getMessage() );
            return 0;
        }

        //#! Extract to root
        $zip = new \ZipArchive();
        if ( $zip->open( $fileSavePath ) !== false ) {
            $zip->extractTo( base_path() );
            $zip->close();
        }
        else {
            $this->line( '>>> An error occurred when trying to extract the downloaded archive. Check for permissions.' );
            return 0;
        }

        //#! Delete temp file
        File::delete( $fileSavePath );

        //#! Trigger the post-install actions
        Artisan::call( 'vp:post-install', [
            //#! Do not delete the uploads directory
            '--d' => false,
        ] );
        $this->line( '>>> ValPress updated to version '.$version );
        return 1;
    }
}
