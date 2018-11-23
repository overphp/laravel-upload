<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午10:33
 */

namespace Overphp\Upload;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadInterface
{
    /**
     * 文件上传
     *
     * @param UploadedFile $file 上传的文件
     * @param array $extensions 允许上传的格式
     * @param int $maxSize 允许上传的大小
     * @param string $pathFormat 存储路径
     * @return array
     */
    public function upload(UploadedFile $file, array $extensions, int $maxSize, string $pathFormat);
}
