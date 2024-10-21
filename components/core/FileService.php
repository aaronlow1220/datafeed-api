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
class FileService
{
    public string $destinationPath;

    /**
     * DatasetService constructor.
     *
     * @param FileRepo $fileRepo
     */
    public function __construct(private FileRepo $fileRepo)
    {
        $this->destinationPath = __DIR__.'/../../runtime/files/original';
    }

    /**
     * upload.
     *
     * @param FileEntity $file
     * @return ActiveRecord
     */
    public function upload(FileEntity $file): ActiveRecord
    {
        if (
            !is_dir($this->destinationPath)
            && !mkdir($this->destinationPath, 0777, true)
        ) {
            throw new Exception("Create directory({$this->destinationPath}) failed");
        }

        $destination = sprintf('%s/%s_%s.%s', $this->destinationPath, date('Ymd'), uniqid(), $file->getExtension());
        if (!copy($file->getFile(), $destination)) {
            throw new Exception('Store file failed');
        }

        try {
            return $this->fileRepo->create([
                'mime' => $file->getMimeType(),
                'extension' => $file->getExtension(),
                'filename' => $file->getName(),
                'path' => $destination,
                'size' => $file->getSize(),
            ]);
        } catch (Exception $e) {
            @unlink($destination);
            @unlink($file->getFile());

            throw new Exception('Create file record, failed', 500, $e);
        }
    }
}
