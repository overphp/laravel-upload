/**
 * 依赖 jQuery File Upload Plugin (https://github.com/blueimp/jQuery-File-Upload) 进行封装的上传插件
 * 依赖 layer 消息提示及加载框
 */
;(function ($, window, document, undefined) {
    var uploadfile = function (element, options) {
        this.element = element;
        this.defaults = {
            type: 'file', /*上传类型 file|image*/
            url: '', /*上传地址*/
            val: '', /*返回value写入地址*/
            src: '', /*指定展现图片的img元素*/
            load: true, /*展示加载层*/
            callback: ''
        };
        this.options = $.extend({}, this.defaults, options);
    };

    uploadfile.prototype = {
        upload: function () {
            var type = this.options.type,
                url = this.options.url,
                valEle = this.options.val,
                srcEle = this.options.src,
                callback = this.options.callback,
                load = this.options.load,
                layerIndex;

            if (url === '') {
                url = type === 'image' ? '/upload/image' : '/upload/file';
            }

            $(this.element).fileupload({
                url: url,
                dataType: 'json',
                start: function () {
                    if (true === load) {
                        layerIndex = layer.load(1);
                    }
                },
                success: function (res) {
                    if (true === load) {
                        layer.close(layerIndex);
                    }
                    if (callback !== '' && typeof callback === 'function') {
                        callback(res);
                    } else {
                        if (res.status) {
                            $(valEle).val(res.url);
                            if (type === 'image') {
                                if (srcEle !== '') {
                                    $(srcEle).attr('src', res.url);
                                }
                            }
                        } else {
                            layer.msg(res.message);
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('upload error:', jqXHR.status, errorThrown);

                    if (true === load) {
                        layer.close(layerIndex);
                    }
                    if (413 === jqXHR.status) {
                        layer.msg('上传文件过大，超过服务器限制');
                    } else {
                        layer.msg('上传出错');
                    }
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        }
    };

    $.fn.upload = function (options) {
        var upload = new uploadfile(this, options);
        return upload.upload();
    }
})(jQuery, window, document);