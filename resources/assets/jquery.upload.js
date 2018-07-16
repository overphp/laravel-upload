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
            view: '', /*指定展现图片的view元素*/
            append: false, /*是否追加到view元素内*/
            class: '', /*img标签class*/
            style: '' /*img 标签样式*/
        };
        this.options = $.extend({}, this.defaults, options);
    };

    uploadfile.prototype = {
        upload: function () {
            var type = this.options.type,
                url = this.options.url,
                valEle = this.options.val,
                srcEle = this.options.src,
                viewEle = this.options.view,
                append = this.options.append,
                class_style = this.options.class,
                style = this.options.style;

            if (url === '') {
                url = type === 'image' ? '/upload/image' : '/upload/file';
            }

            $(this.element).fileupload({
                url: url,
                dataType: 'json',
                done: function (e, data) {
                    if (data.result.status) {
                        $(valEle).val(data.result.url);
                        if (type === 'image') {
                            if (srcEle !== '') {
                                $(srcEle).attr('src', data.result.url);
                            } else if (viewEle !== '') {
                                var text = '<img class="' + class_style + '" style="' + style + '" src="' + data.result.url + '">';
                                if (append) {
                                    $(viewEle).append(text);
                                } else {
                                    $(viewEle).html(text);
                                }
                            }
                        }
                    } else {
                        alert(data.result.message);
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