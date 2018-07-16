/**
 * 依赖 jQuery File Upload Plugin (https://github.com/blueimp/jQuery-File-Upload) 进行封装的上传插件
 */
;(function ($, window, document, undefined) {
    var uploadfile = function (element, options) {
        this.element = element;
        this.defaults = {
            type: 'file', /*上传类型 file|image*/
            url: '', /*上传地址*/
            val: '', /*返回value写入地址*/
            src: '', /*指定展现图片的img元素*/
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
                callback = this.options.callback;

            if (url === '') {
                url = type === 'image' ? '/upload/image' : '/upload/file';
            }

            $(this.element).fileupload({
                url: url,
                dataType: 'json',
                done: function (e, data) {
                    if (callback !== '' && typeof callback === 'function') {
                        callback(data.result);
                    } else {
                        if (data.result.status) {
                            $(valEle).val(data.result.url);
                            if (type === 'image') {
                                if (srcEle !== '') {
                                    $(srcEle).attr('src', data.result.url);
                                }
                            }
                        } else {
                            alert(data.result.message);
                        }
                    }
                }
                ,
                progressall: function () {
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