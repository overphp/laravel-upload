## Laravel-upload 文件上传扩展包
`laravel-upload` 是一个用于文件异步上传的扩展包。
## 安装
#### 安装包

 ```
 composer require "overphp/upload":"1.*"
 ```

在终端执行composer update命令

```
composer update --prefer-dist
```

发布资源文件

```
// 发布包文件
php artisan vendor:publish --tag=laravel-upload
// 发布jquery上传插件（需要使用jquery.upload.js插件时发布）
php artisan vendor:publish --tag=laravel-upload-assets
```

## 使用
### 参数配置
参数配置参考 `config/upload.php` 及注释内容

### 使用中间件实现上传权限管理
默认任何人可以上传文件,如果需要进行上传权限控制，请自行定义中间件，并在 `config/upload.php` 中修改 middleware 的值为定义的中间件名称。


```
 /**
   * 上传模块认证 middleware
   *
   * 文件上传需要的中间件名称，为空则允许任何用户上传文件
   */
   'middleware' => [],
```
 
### 仅使用上传后端
#### 上传组件返回值示例
默认为`json`格式

```
{
    "status": true, // 上传结果  true | false
    "message": "Uploaded successfully.", 
    "url": "/storage/file/20180717/63320412.png", 
    "name": "63320412.png",
    "original": "avatar5.png",
    "extension": ".png",
    "size": 7578
}
```
#### 上传地址
- 文件上传url：`upload/file` 
- 图片上传url: `upload/image`

#### 自定义
请参考 `Overphp\Upload\Upload.php` 文件以及 `Overphp\Upload\UploadController.php` 文件。

### 结合jquery.upload.js插件使用
`jquery.upload.js` 上传插件在 [jquery.fileupload.js](https://github.com/blueimp/jQuery-File-Upload) 插件基础上进行了简单封装。 

#### 发布jquery.upload.js文件
如果需要修改插件发布的路径,可以修改`upload.assets_path`为需要的路径即可。

```
php artisan vendor:publish --tag=laravel-upload-assets
```

#### 使用插件
##### 文件上传

```
<input type="hidden" id="file">
<input type="file" id="upfile" name="upfile">

// include jquery.js
@include('upload::upload')
<script>
	$(function(){
		$('#upfile').upload({val:'#file'});
	);
</script>
```

##### 图片上传

```
<img src="" id="image">
<input type="hidden" id="file">
<input type="file" id="upfile" name="upfile">

// include jquery.js
@include('upload::upload')
<script>
	$(function(){
		$('#upfile').upload({
			type:'image' // 默认file，修改为 图片模式
			val:'#file', // 图片路径写入表单
			src:'#image' // 返回的图片路径写入img标签
		});
	);
</script>
```

#### 自定义回调

```
<input type="hidden" id="file">
<input type="file" id="upfile" name="upfile">

// include jquery.js
@include('upload::upload')
<script>
	$(function(){
		$('#upfile').upload({
			callback:function(res){
				// do something here
				// res 为json格式返回值
				// callback时，除了type参数，其余参数无效
			}
		});
	);
</script>
```

#### jquery.upload.js 参数说明
`type`

文件上传类型，默认：`file`,可选值:`image`,`file`。

`url`

文件上传url地址，默认为空，不为空时`type`参数无效。

`val`

文件上传成功后返回的文件url地址写入对象。

`src`

图片文件上传成功后返回的图片url地址写入 `img`标签对象，仅`type`为`image`时有效。

`callback`

自定义返回结果处理，使用本参数时，除了`type`属性，其他全部无效。






