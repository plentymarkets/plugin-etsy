<?php

$files = glob(sys_get_temp_dir()."Etsy*");
$now   = time();

foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= 60 * 60 * 3) { // 3h
            unlink($file);
        }
    }
}