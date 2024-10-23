<?php

namespace app\components;

use Exception;
use yii\web\UploadedFile;

/**
 * This is a file object for upload data of Tables.
 *
 * @author Noah Wang <noah.wang@atelli.ai>
 */
class FileEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $file;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * UploadedFile constructor.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    public function __construct(array $data)
    {
        $names = ['name', 'file', 'mimeType'];
        foreach ($names as $name) {
            if (!isset($data[$name])) {
                throw new Exception("Undefined {$name} in data");
            }
            $this->{$name} = $data[$name];
        }
    }

    /**
     * create by UploadedFile.
     *
     * @param UploadedFile $uploadedfile
     * @return FileEntity
     */
    public static function createByUploadedFile(UploadedFile $uploadedfile): FileEntity
    {
        return new self([
            'name' => $uploadedfile->name,
            'file' => $uploadedfile->tempName,
            'mimeType' => $uploadedfile->type,
        ]);
    }

    /**
     * get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * get file path.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * get filename extension.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    /**
     * get mime type.
     *
     * @return int
     */
    public function getSize(): int
    {
        return filesize($this->getFile());
    }

    /**
     * get mime type.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
