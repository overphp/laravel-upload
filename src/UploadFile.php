<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午7:55
 */

namespace Overphp\Upload;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFile implements UploadInterface
{

    /**
     * @var UploadedFile
     */
    protected $file;

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
     * UploadFile constructor.
     * @param string $disk
     */
    public function __construct(string $disk = 'public')
    {
        $this->storage = Storage::disk($disk);

        $this->messages = [
            UPLOAD_ERR_INI_SIZE => trans('upload::upload.err_ini_size'),
            UPLOAD_ERR_FORM_SIZE => trans('upload::upload.err_form_size'),
            UPLOAD_ERR_PARTIAL => trans('upload::upload.err_partial'),
            UPLOAD_ERR_NO_FILE => trans('upload::upload.err_no_file'),
            UPLOAD_ERR_CANT_WRITE => trans('upload::upload.err_cant_write'),
            UPLOAD_ERR_NO_TMP_DIR => trans('upload::upload.err_no_tmp_dir'),
            UPLOAD_ERR_EXTENSION => trans('upload::upload.err_extension'),
            'upload_err_unknown' => trans('upload::upload.err_unknown'),
            'upload_success' => trans('upload::upload.success')
        ];
    }

    /**
     * 文件上传
     *
     * @param UploadedFile $file 上传的文件
     * @param array $extensions 允许上传的格式
     * @param int $maxSize 允许上传的大小
     * @param string $pathFormat 存储路径
     * @return array
     */
    public function upload(UploadedFile $file, array $extensions, int $maxSize, string $pathFormat)
    {
        logger('upload params', func_get_args());
        $this->file = $file;
        $this->extensions = $extensions;
        $this->maxSize = $maxSize;
        $this->pathFormat = $pathFormat;

        $this->doUpload();

        return $this->getFileInfo();
    }

    /**
     * 执行上传
     */
    protected function doUpload()
    {
        // 1. 校验文件上传是否成功
        if (empty($this->file) || !$this->file->isValid()) {
            $this->error = $this->file->getError();
            return;
        }

        // 2. 设置文件名等
        $this->fileSize = $this->file->getSize();
        $this->fileExtension = '.' . $this->file->getClientOriginalExtension();
        $this->originalName = $this->file->getClientOriginalName();
        $this->fileName = $this->getRandomFileName();

        // 3. 校验文件大小和扩展名
        if (!$this->checkFileSize() || !$this->checkFileExtension()) {
            return;
        }

        // 4.保存文件
        try {
            $path = $this->storage->putFileAs($this->getStoragePath(), $this->file, $this->fileName);
            $this->fileUrl = $this->storage->url($path);
        } catch (FileException $e) {
            $this->error = $this->file->getError();
        } catch (\Exception $e) {
            $this->error = '';
        }
    }

    /**
     * 检测文件大小是否超过配置设置限制
     *
     * @return bool
     */
    protected function checkFileSize()
    {
        if ($this->fileSize > $this->maxSize) {
            $this->error = UPLOAD_ERR_FORM_SIZE;
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
            $this->error = UPLOAD_ERR_EXTENSION;
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
            return $this->messages[$this->error] ?? $this->messages['upload_err_unknown'];
        }
        return $this->messages['upload_success'];
    }

    /**
     * 返回上传结果
     *
     * @return array
     */
    protected function getFileInfo()
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
}
