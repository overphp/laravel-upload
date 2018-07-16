<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午10:46
 */

namespace Overphp\Upload;

use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    protected $service;

    /**
     * UploadController constructor.
     * @param $service
     */
    public function __construct(UploadFile $service)
    {
        $middleware = config('upload.middleware');
        if (!empty($middleware)) {
            $this->middleware = $middleware;
        }
        $this->service = $service;
    }

    /**
     * 文件上传
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function file()
    {
        $config = config('upload.file');
        $result = $this->service->upload($config);

        return response()->json($result);
    }

    /**
     * 图片上传
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function image()
    {
        $config = config('upload.image');
        $result = $this->service->upload($config);

        return response()->json($result);
    }
}
