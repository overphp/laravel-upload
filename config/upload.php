<?php
/**
 * Created by PhpStorm.
 * User: overphp
 * Github: https://github.com/overphp
 * Date: 2018/7/16
 * Time: 下午7:50
 */

return [

    /**
     * 上传模块认证 middleware
     *
     * 文件上传需要的中间件名称，为空则允许任何用户上传文件
     */
    'middleware' => [],

    /**
     * 自定义域名前缀
     * 一般为：protocal://hostname:port 如: http://127.0.0.1
     */
    'url_prefix' => env('UPLOAD_URL_PREFIX', ''),

    /**
     * 上传文件的软连接目录，相对public_path()
     *
     * 上传文件返回的url组成方式: url_prefix + storage_link_dir + path_format
     */
    'storage_link_dir' => env('STORAGE_LINK_DIR', 'storage'),

    /**
     * 静态资源文件发布目录，相对public_path()
     */
    'assets_path' => 'static/plugins/upload',

    /**
     * fieldName: 上传文件表单输入框名称
     * maxSize: 允许上传的最大尺寸(必须小于php.ini 以及服务器(nginx|apache)设置的最大值)
     * allowFiles: 允许上传的文件扩展名
     * pathFormat 文件命名
     *
     * {rand:6} 会替换成随机数,后面的数字是随机数的位数
     * {time} 会替换成时间戳
     * {yyyy} 会替换成四位年份
     * {yy} 会替换成两位年份
     * {mm} 会替换成两位月份
     * {dd} 会替换成两位日期
     * {hh} 会替换成两位小时
     * {ii} 会替换成两位分钟
     * {ss} 会替换成两位秒
     * 非法字符 \ : * ? ' < > |
     *
     */

    // 文件上传配置
    'file' => [
        'fieldName' => 'upfile',
        'maxSize' => 8388608,//8M
        'allowFiles' => [
            '.png', '.jpg', '.jpeg', '.gif', '.bmp',
            '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg',
            '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid',
            '.rar', '.zip', '.tar', '.gz', '.7z', '.bz2', '.cab', '.iso',
            '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.txt', '.md', '.xml'
        ],
        'pathFormat' => 'file/{yyyy}{mm}{dd}/{rand:8}',
    ],

    // 图片上传配置
    'image' => [
        'fieldName' => 'upfile',
        'maxSize' => 2097152,//2M
        'allowFiles' => ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],
        'pathFormat' => 'image/{yyyy}{mm}{dd}/{rand:8}'
    ],
];