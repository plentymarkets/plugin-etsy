<?php

namespace Etsy\Crons;


use Etsy\Helper\SettingsHelper;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;


class ImageFileCleanupCron
{
    const IMAGE_FILE_CLEANUP = 'clean_image_temp_dir';
    /**
     * @var LibraryCallContract
     */
    private $libraryCall;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * ImageFileCleanupCron constructor.
     * @param LibraryCallContract $libraryCall
     */
    public function __construct(LibraryCallContract $libraryCall, ConfigRepository $configRepository)
    {
        $this->libraryCall = $libraryCall;
        $this->configRepository = $configRepository;
    }
    
    public function handle()
    {
        if($this->checkIfCanRun() === 'true') return;

        $this->libraryCall->call(self::IMAGE_FILE_CLEANUP);
    }

    /**
     * Return if we can run this cron or is disabled
     *
     * @return string
     */
    private function checkIfCanRun(): string
    {
        return $this->configRepository->get(SettingsHelper::PLUGIN_NAME . '.imageCleanup', 'true');
    }
}
