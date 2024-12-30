<?php

namespace app\commands;

use Throwable;
use yii\console\Controller;

/**
 * This controller is to delete original files.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FileController extends Controller
{
    /**
     * Delete original files that are older than 180 days.
     *
     * @return void
     */
    public function actionDeleteOriginalFiles()
    {
        $days = 180;

        $originalFilePath = __DIR__.'/../runtime/files/original/';
        if (!is_dir($originalFilePath)) {
            echo "Directory not found: {$originalFilePath}\n";

            return;
        }

        $files = array_values(preg_grep('/^([^.])/', scandir($originalFilePath)));

        $now = time();
        $maxTime = $days * 24 * 60 * 60; // 1 day
        foreach ($files as $file) {
            $filePath = $originalFilePath.$file;
            $fileTime = filectime($filePath);
            $fileExistedTime = $now - $fileTime;
            if ($fileExistedTime > $maxTime) {
                try {
                    unlink($filePath);
                    $fileExistedTime = round($fileExistedTime / 60 / 60 / 24);
                    echo "Deleted: {$file}\t Existed for {$fileExistedTime} days\n";
                } catch (Throwable $e) {
                    echo "Error deleting file: {$file}\n";
                }
            }
        }
    }
}
