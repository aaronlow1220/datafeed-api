<?php

namespace app\components\core;

use Exception;
use app\components\FileEntity;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

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

        $fileName = uniqid().'_'.pathinfo($file->getName(), PATHINFO_FILENAME).'.'.$file->getExtension();
        $dest = sprintf('%s/%s', $this->destPath, $fileName);
        if (!copy($file->getFile(), $dest)) {
            throw new Exception('Store file, failed');
        }

        try {
            return $this->fileRepo->create([
                'mime' => $file->getMimeType(),
                'extension' => $file->getExtension(),
                'filename' => $fileName,
                'path' => $dest,
                'size' => $file->getSize(),
            ]);
        } catch (Exception $e) {
            @unlink($dest);
            @unlink($file->getFile());

            throw new Exception('Create file record, failed', 500, $e);
        }
    }

    /**
     * Load url filefeed to UploadedFile.
     *
     * @param string $url
     * @return UploadedFile
     */
    public function loadFileToUploadedFile($url)
    {
        $fileContent = file_get_contents($url);

        if (false === $fileContent) {
            throw new Exception("Could not read file from {$url}");
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($tempFilePath, $fileContent);

        $mimeToExt = [
            'text/csv' => 'csv',
            'text/xml' => 'xml',
            'text/plain' => 'txt',
        ];

        $extension = $mimeToExt[mime_content_type($tempFilePath)] ?? 'txt';

        $uploadedFile = new UploadedFile();
        $uploadedFile->name = basename($url).'.'.$extension;
        $uploadedFile->tempName = $tempFilePath;
        $uploadedFile->type = mime_content_type($tempFilePath);
        $uploadedFile->size = filesize($tempFilePath);
        $uploadedFile->error = UPLOAD_ERR_OK;
        $uploadedFile->fullPath = basename($url).'.'.$extension;

        return $uploadedFile;
    }
}
