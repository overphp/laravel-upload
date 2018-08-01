<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午7:55
 */

namespace Overphp\Upload;

class UploadFile extends Upload
{
    /**
     * 文件校验及保存
     */
    protected function doUpload()
    {
        $this->file = $this->request->file($this->fileField);

        // 1. 校验文件上传是否成功
        if (!$this->file->isValid()) {
            $this->error = $this->file->getError();
            return;
        }

        // 2.1 设置文件名等
        $this->fileSize = $this->file->getSize();
        $this->fileExtension = '.' . $this->file->guessExtension();
        $this->originalName = $this->file->getClientOriginalName();

        // 2.2 校验文件大小和扩展名
        if (!$this->checkFileSize() || !$this->checkFileExtension()) {
            return;
        }

        // 4.保存文件
        try {
            $path = $this->storage->putFile($this->getStoragePath(), $this->file);
            $this->fileUrl = $this->storage->url($path);
            $this->fileName = basename($path);
        } catch (FileException $e) {
            $this->error = $this->file->getError();
        }
    }
}
