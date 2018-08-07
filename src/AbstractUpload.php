<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午10:38
 */

namespace Overphp\Upload;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractUpload implements UploadInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * 上传错误
     *
     * @var string
     */
    protected $error;

    /**
     * 存储磁盘
     *
     * @var string
     */
    protected $disk = 'public';

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * 上传配置
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var UploadedFile
     */
    protected $file;

    /**
     * 上传表单字段
     *
     * @var string
     */
    protected $fileField;

    /**
     * 允许上传的文件类型(扩展名),带"."
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * 允许上传的最文件大小（单位：bit）
     *
     * @var int
     */
    protected $maxFileSize;

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
     * Upload constructor.
     */
    public function __construct()
    {
        $this->disk = config('upload.disk') ?? $this->disk;
        $this->storage = Storage::disk($this->disk);
    }

    /**
     * 文件上传
     *
     * @param array $config
     * @return array
     */
    public function upload(array $config)
    {
        $this->request = request();
        logger('file upload request', $this->request->all());

        $this->config = $config;
        $this->fileField = $config['fieldName'];
        $this->extensions = $config['allowFiles'] ?? [];
        $this->maxFileSize = $config['maxSize'] ?? 0;

        $this->doUpload();
        return $this->getFileInfo();
    }

    /**
     * 文件校验及保存
     */
    abstract protected function doUpload();

    /**
     * 检测文件大小是否超过配置设置限制
     *
     * @return bool
     */
    protected function checkFileSize()
    {
        if ($this->fileSize > $this->maxFileSize) {
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
            $format = $this->config['pathFormat'];
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
        $messages = [
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

        if ($this->error) {
            return $messages[$this->error] ?? $messages['upload_err_unknown'];
        }
        return $messages['upload_success'];
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
        logger('file upload result', $info);
        return $info;
    }
}
