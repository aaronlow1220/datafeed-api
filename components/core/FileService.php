<?php

namespace app\components\core;

use Exception;
use app\components\FileEntity;
use yii\db\ActiveRecord;

/**
 * This service is used to handle specific business logic for File.
 *
 * @author Noah Wang <noah.wang@atelli.ai>
 */
final class FileService
{
    /**
     * @var string
     */
    private string $destPath;

    /**
     * construct.
     *
     * @param FileRepo $fileRepo
     * @return void
     */
    public function __construct(private FileRepo $fileRepo)
    {
        $this->destPath = __DIR__.'/../../runtime/files/original';
    }

    /**
     * upload.
     *
     * @param FileEntity $file
     * @return ActiveRecord
     */
    public function upload(FileEntity $file): ActiveRecord
    {
        if (!is_dir($this->destPath) && !mkdir($this->destPath, 0777, true)) {
            throw new Exception("Create directory({$this->destPath}), failed");
        }

        $dest = sprintf('%s/%s_%s_feed.%s', $this->destPath, uniqid(), $file->getName(), $file->getExtension());
        if (!copy($file->getFile(), $dest)) {
            throw new Exception('Store file, failed');
        }

        try {
            return $this->fileRepo->create([
                'mime' => $file->getMimeType(),
                'extension' => $file->getExtension(),
                'filename' => $file->getName(),
                'path' => $dest,
                'size' => $file->getSize(),
            ]);
        } catch (Exception $e) {
            @unlink($dest);
            @unlink($file->getFile());

            throw new Exception('Create file record, failed', 500, $e);
        }
    }
}
