<?php

namespace app\commands;

use Throwable;
use app\components\core\FileRepo;
use yii\console\Controller;

/**
 * This controller is to delete original files.
 *
 * @author Aaron Low <aaron.low@atelli.ai>
 */
class FileController extends Controller
{
    /**
     * @var string
     */
    public $original;

    /**
     * @var string
     */
    public $result;

    /**
     * Declared options.
     *
     * @param string $actionID
     * @return string[]
     */
    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'original',
            'result',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, string>
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'o' => 'original',
            'r' => 'result',
        ]);
    }

    /**
     * Delete original files that are older than 180 days.
     *
     * @param FileRepo $fileRepo
     * @return void
     */
    public function actionDeleteFiles(FileRepo $fileRepo)
    {
        $days = 180;

        $filePath = __DIR__.'/../runtime/files/original/';

        if ($this->result) {
            $filePath = __DIR__.'/../runtime/files/result/';
        }

        if (!is_dir($filePath)) {
            echo "Directory not found: {$filePath}\n";

            return;
        }

        $files = array_values(preg_grep('/^([^.])/', scandir($filePath)));

        if (empty($files)) {
            echo "No files found in directory: {$filePath}\n";

            return;
        }

        $now = time();
        $maxTime = $days * 24 * 60 * 60; // 1 day
        $deletedFiles = 0;
        foreach ($files as $file) {
            $filePathTmp = $filePath.$file;
            $fileTime = filectime($filePath);
            $fileExistedTime = $now - $fileTime;
            if ($fileExistedTime > $maxTime) {
                try {
                    unlink($filePathTmp);
                    $fileRepo->update(['filename' => $file], ['deleted_at' => $now]);
                    $fileExistedTime = round($fileExistedTime / 60 / 60 / 24);
                    echo "Deleted: {$file}\t Existed for {$fileExistedTime} days\n";
                    ++$deletedFiles;
                } catch (Throwable $e) {
                    echo "Error deleting file: {$file}\n";
                }
            }
        }

        echo "Deleted {$deletedFiles} files\n";
    }
}
