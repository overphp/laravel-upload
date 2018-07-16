<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午10:33
 */

namespace Overphp\Upload;


interface UploadInterface
{
    /**
     * 文件上传
     *
     * @param array $config
     * @return array
     */
    public function upload(array $config);
}
