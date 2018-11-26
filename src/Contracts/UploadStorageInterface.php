<?php

namespace Overphp\Upload\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadStorageInterface
{
    /**
     * @param int $fileSize
     * @param string $extension
     * @param string|null $originalName
     * @return void
     */
    public function setFile(int $fileSize, string $extension, string $originalName = null);

    /**
     * @param string $contents
     * @return void
     */
    public function saveContents(string $contents);

    /**
     * @param UploadedFile $file
     * @return void
     */
    public function saveUploadedFile(UploadedFile $file);

    /**
     * @return array
     */
    public function getFileInfo();

    /**
     * 校验是否上传的文件
     *
     * @param $file
     * @return bool
     */
    public function validateUploadedFile($file);
}
