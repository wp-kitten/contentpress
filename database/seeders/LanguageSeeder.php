<?php
namespace Database\Seeders;


use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $languagesFilePath = resource_path( 'languages.json' );
        if ( !File::isFile( $languagesFilePath ) ) {
            exit( "The languages file was not found, expected: " . $languagesFilePath );
        }
        $languages = File::get( $languagesFilePath );
        if ( empty( $languages ) ) {
            exit( "The languages file is empty: " . $languagesFilePath );
        }
        $languages = json_decode( $languages, true );
        if ( !$languages ) {
            exit( "The languages file does not have the expected json format: " . $languagesFilePath );
        }

        foreach ( $languages as $code => $name ) {
            if(! Language::where('code', $code)->first()) {
                Language::create( [
                    'code' => $code,
                    'name' => $name,
                ] );
            }
        }
    }
}
