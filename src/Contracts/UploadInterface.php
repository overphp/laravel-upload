<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午10:33
 */

namespace Overphp\Upload\Contracts;

interface UploadInterface
{
    /**
     * @param array $config
     * @return array
     */
    public function upload(array $config);
}
