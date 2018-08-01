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
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class Upload implements UploadInterface
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
     * 文件在服务器中完整目录路径
     *
     * @var string
     */
    protected $fullStoragePath;

    /**
     * url路径
     *
     * @var string
     */
    protected $fileUrl;

    /**
     * 存储路径：相对于storage文件夹
     *
     * @var string
     */
    protected $storagePath = 'app/public';

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

        $config['url_prefix'] = $config['url_prefix'] ?? config('upload.url_prefix');
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
        if (!in_array($this->fileExtension, $this->extensions)) {
            $this->error = UPLOAD_ERR_EXTENSION;
            return false;
        }
        return true;
    }

    /**
     * 上传文件重命名
     *
     * @param bool $renew 重命名
     */
    protected function rename($renew = false)
    {
        if (empty($this->fileName) || $renew) {
            //替换日期格式
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

            //替换随机字符串
            $randNum = rand(1, 10000000000) . rand(1, 10000000000);
            if (preg_match('/\{rand\:([\d]*)\}/i', $format, $matches)) {
                $format = preg_replace('/\{rand\:[\d]*\}/i', substr($randNum, 0, $matches[1]), $format);
            }

            //md5
            $md5 = substr(md5($t . $randNum), 0, 16);
            $format = str_replace('{md5}', $md5, $format);
            $format = trim($format . $this->fileExtension, '/');

            // 完整路径
            $path = storage_path($this->storagePath . DIRECTORY_SEPARATOR . $format);

            $this->setFileUrl($format); // 设置url
            $this->fileName = basename($path); // 新文件名
            $this->fullStoragePath = dirname($path); // 完成保存路径
        }
    }

    /**
     * 设置上传文件的url
     */
    protected function setFileUrl($format)
    {
        $url = rtrim($this->config['url_prefix'], '/');
        $url .= '/' . trim(config('upload.storage_link_dir'), '/');
        $url .= '/' . $format;

        $this->fileUrl = $url;
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
