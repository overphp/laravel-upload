<?php

namespace Overphp\Upload;

use Illuminate\Support\Facades\Storage;
use Overphp\Upload\Contracts\UploadStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadStorage implements UploadStorageInterface
{

    const UPLOAD_ERR_UNKNOWN = 'upload_err_unknown';

    const UPLOAD_SUCCESS = 'upload_success';

    /**
     * @var string
     */
    protected $pathFormat;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var int
     */
    protected $maxSize;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * 上传错误
     *
     * @var string
     */
    protected $error;

    /**
     * 上传文件的扩展名,带"."
     *
     * @var string
     */
    protected $fileExtension;

    /**
     * 上传文件的大小
     *
     * @var int
     */
    protected $fileSize;

    /**
     * 上传文件的原始名称
     *
     * @var string
     */
    protected $originalName;

    /**
     * 上传文件新名称
     *
     * @var string
     */
    protected $fileName;

    /**
     * url路径
     *
     * @var string
     */
    protected $fileUrl;

    /**
     * 存储相对路径
     *
     * @var string
     */
    protected $storagePath;

    /**
     * UploadStorage constructor.
     * @param string $disk
     */
    public function __construct(int $maxSize, array $extensions, string $pathFormat, string $disk = 'public')
    {
        $this->maxSize = $maxSize;
        $this->extensions = $extensions;
        $this->pathFormat = $pathFormat;

        $this->storage = Storage::disk($disk);

        $this->messages = [
            UPLOAD_ERR_INI_SIZE => trans('upload::upload.err_ini_size'),
            UPLOAD_ERR_FORM_SIZE => trans('upload::upload.err_form_size'),
            UPLOAD_ERR_PARTIAL => trans('upload::upload.err_partial'),
            UPLOAD_ERR_NO_FILE => trans('upload::upload.err_no_file'),
            UPLOAD_ERR_CANT_WRITE => trans('upload::upload.err_cant_write'),
            UPLOAD_ERR_NO_TMP_DIR => trans('upload::upload.err_no_tmp_dir'),
            UPLOAD_ERR_EXTENSION => trans('upload::upload.err_extension'),
            self::UPLOAD_ERR_UNKNOWN => trans('upload::upload.err_unknown'),
            self::UPLOAD_SUCCESS => trans('upload::upload.success')
        ];
    }

    /**
     * @param int $fileSize
     * @param string $extension
     * @param string|null $originalName
     * @return void
     */
    public function setFile(int $fileSize, string $extension, string $originalName = null)
    {
        $this->fileSize = $fileSize;
        $this->fileExtension = $extension;
        $this->originalName = $originalName;

        // 校验文件大小和扩展名
        if (!$this->checkFileSize() || !$this->checkFileExtension()) {
            return;
        }
    }

    /**
     * @param string $contents
     * @return void
     */
    public function saveContents(string $contents)
    {
        $this->fileName = $this->getRandomFileName();
        $filename = $this->getStoragePath() . DIRECTORY_SEPARATOR . $this->fileName;
        $this->storage->put($filename, $contents);
        $this->fileUrl = $this->storage->url($filename);
    }

    /**
     * @param UploadedFile $file
     * @return void
     */
    public function saveUploadedFile(UploadedFile $file)
    {
        try {
            $this->fileName = $this->getRandomFileName();
            $path = $this->storage->putFileAs($this->getStoragePath(), $file, $this->fileName);
            $this->fileUrl = $this->storage->url($path);
        } catch (\Exception $e) {
            $this->error = $file->getError();
        }
    }

    /**
     * @return array
     */
    public function getFileInfo()
    {
        $info = [
            'status' => $this->error == null ? true : false,
            'message' => $this->getMessage(),
            'url' => $this->fileUrl,
            'name' => $this->fileName,
            'original' => $this->originalName,
            'extension' => $this->fileExtension,
            'size' => $this->fileSize
        ];

        logger('upload result', $info);
        return $info;
    }

    /**
     * 校验是否上传的文件
     *
     * @param $file
     * @return bool
     */
    public function validateUploadedFile($file)
    {
        if (empty($file) || !($file instanceof UploadedFile)) {
            $this->setError(UPLOAD_ERR_NO_FILE);
            return false;
        }

        if (!$file->isValid()) {
            $this->setError($file->getError());
            return false;
        }
        return true;
    }

    /**
     * 检测文件大小是否超过配置设置限制
     *
     * @return bool
     */
    protected function checkFileSize()
    {
        if ($this->fileSize > $this->maxSize) {
            $this->setError(UPLOAD_ERR_FORM_SIZE);
            return false;
        }
        return true;
    }

    /**
     * 检测文件格式是否被允许上传
     *
     * @return bool
     */
    protected function checkFileExtension()
    {
        if (!in_array(strtolower($this->fileExtension), $this->extensions)) {
            $this->setError(UPLOAD_ERR_EXTENSION);
            return false;
        }
        return true;
    }

    /**
     * 获取存储路径
     *
     * @return string
     */
    protected function getStoragePath()
    {
        if (empty($this->storagePath)) {
            $t = time();
            $d = explode('-', date('Y-y-m-d-H-i-s'));
            $format = $this->pathFormat;
            $format = str_replace('{yyyy}', $d[0], $format);
            $format = str_replace('{yy}', $d[1], $format);
            $format = str_replace('{mm}', $d[2], $format);
            $format = str_replace('{dd}', $d[3], $format);
            $format = str_replace('{hh}', $d[4], $format);
            $format = str_replace('{ii}', $d[5], $format);
            $format = str_replace('{ss}', $d[6], $format);
            $format = str_replace('{time}', $t, $format);
            $this->storagePath = trim($format, '/');
        }
        return $this->storagePath;
    }

    /**
     * 获取随机文件名
     *
     * @return string
     */
    protected function getRandomFileName()
    {
        return str_random(40) . $this->fileExtension;
    }

    /**
     * 获取上传结果消息
     *
     * @return string
     */
    protected function getMessage()
    {
        if ($this->error) {
            return $this->messages[$this->error] ?? $this->messages[self::UPLOAD_ERR_UNKNOWN];
        }

        return $this->messages[self::UPLOAD_SUCCESS];
    }

    /**
     * 设置错误信息
     *
     * @param string|int $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * 上传保存文件
     *
     * @param UploadedFile $file
     */
    public function uploadUploadedFile(UploadedFile $file)
    {
        $this->setFile(
            $file->getSize(),
            '.' . $file->getClientOriginalExtension(),
            $file->getClientOriginalName()
        );

        $this->saveUploadedFile($file);
    }
}
