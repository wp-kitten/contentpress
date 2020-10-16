<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class MediaHelper
 * @package App\Helpers
 */
class MediaHelper
{
    /**
     * Holds the system path to the uploads directory
     * @var string
     */
    private $uploadsDir;

    /**
     * Holds the HTTP path to the uploads directory
     * @var string
     */
    private $uploadsUrl;

    /**
     * MediaHelper constructor.
     */
    public function __construct()
    {
        $this->uploadsDir = wp_normalize_path( untrailingslashit( public_path( 'uploads/files' ) ) );
        $this->uploadsUrl = asset( 'uploads/files' );
        File::ensureDirectoryExists( $this->uploadsDir, 0777, true );
    }

    /**
     * Retrieve the url to the specified file path
     * @param string $filePath
     * @return string
     */
    public function getUrl( string $filePath ): string
    {
        $basePath = str_ireplace( $this->uploadsDir, '', wp_normalize_path( $filePath ) );
        return wp_normalize_path( untrailingslashit( $this->uploadsUrl ) . '/' . $basePath );
    }

    /**
     * Retrieve the base dir path for the specified  file path
     * @param string $filePath
     * @return mixed
     */
    public function getBaseUploadPath( string $filePath )
    {
        return str_ireplace( $this->uploadsDir, '', wp_normalize_path( $filePath ) );
    }

    /**
     * Retrieve the system path to the specified file
     * @param string $fileBasePath
     * @return string
     */
    public function getPath( string $fileBasePath ): string
    {
        return path_combine( $this->uploadsDir, $fileBasePath );
    }

    /**
     * Retrieve the system path to the uploads dir
     * @return string
     */
    public function getUploadsDir(): string
    {
        return $this->uploadsDir;
    }

    /**
     * Retrieve the HTTP path to the uploads dir
     * @return string
     */
    public function getUploadsUrl(): string
    {
        return untrailingslashit( $this->uploadsUrl );
    }

    /**
     * Check to see whether or not the specified $path is pointing to the uploads directory
     * @param string $path The file path to check
     * @return bool
     */
    public function isValidUploadsDirPath( string $path ): bool
    {
        $path = wp_normalize_path( realpath( $path ) );
        return Str::contains( $path, $this->uploadsDir );
    }
}
