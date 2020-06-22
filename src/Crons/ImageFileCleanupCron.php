<?php

namespace Etsy\Crons;


use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;

class ImageFileCleanupCron
{
    const IMAGE_FILE_CLEANUP = 'clean_image_temp_dir';
    /**
     * @var LibraryCallContract
     */
    private $libraryCall;

    /**
     * ImageFileCleanupCron constructor.
     * @param LibraryCallContract $libraryCall
     */
    public function __construct(LibraryCallContract $libraryCall)
    {
        $this->libraryCall = $libraryCall;
    }
    
    public function handle()
    {
        $this->libraryCall->call(self::IMAGE_FILE_CLEANUP);
    }
}