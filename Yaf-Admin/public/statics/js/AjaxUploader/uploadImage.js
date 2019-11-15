/**
 * 上传图片。
 * @param String  filesUrl   图片文件域名。
 * @param String  baseJsUrl  JS静态资源所在位置。
 * @param String  ImgId      展示图片的ID。
 * @param String  saveImgId  图片上传成功时，图片URL保存的地方。
 * @param Integer width      图片展示时的宽度。并非裁剪宽度。
 * @param Integer height     图片展示时的高度。并非裁剪的高度。
 * @param string  sizeType   图片类型尺寸。
 */
function uploadImage(filesUrl, baseJsUrl, ImgId, saveImgId, width, height, uploadUrl, sizeType) {
  if (sizeType == undefined) {
    sizeType = '';
  }

	var previewImage = $('#' + ImgId);
	var default_img = $('#'+saveImgId).val();
    imgurl = default_img;
	var imageUrl = default_img.length > 0 ? imgurl : baseJsUrl + 'AjaxUploader/upload_default' + sizeType + '.png';
    previewImage.empty();
    preWidth  = width + 4; // 边框宽度部分导致边框被图片遮住了。所以，边框部分的尺寸要比图片大4像素。
    preHeight = height + 4; // 同上。
	previewImage.append('<img width="' + width + '" height="' + height + '" src="' + imageUrl + '">');
	previewImage.css({"width": preWidth + "px", "height": preHeight + "px", "border": "2px solid #CCD"});
    var uploader = new ss.SimpleUpload({
      button: previewImage,
      url: uploadUrl,
      name: 'uploadfile',
      multipart: true,
      hoverClass: 'hover',
      focusClass: 'focus',
      responseType: 'json',
      startXHR: function() {
          // 开始上传。可以做一些初始化的工作。
      },
      onSubmit: function() {
    	  previewImage.empty();
    	  previewImage.append('<img style="width:' + width + 'px;height:' + height + 'px;" src="' + baseJsUrl + 'AjaxUploader/upload_loading' + sizeType + '.png">');
        },
      onComplete: function(filename, response) {
          // 上传完成。
          if (!response) {
        	  previewImage.empty();
        	  previewImage.append('<img style="width:' + width + 'px;height:' + height + 'px;" src="' + baseJsUrl + 'AjaxUploader/upload_error' + sizeType + '.png">');
              return;
          }
          if (response.code == 200) {
        	  previewImage.empty();
        	  previewImage.append('<img style="width:' + width + 'px;height:' + height + 'px;" style="width:' + width + 'px;height:' + height + 'px;" src="' + response.data['image_url'] + '"/>');
              $('#' + saveImgId).val(response.data['image_url']);
          } else {
        	  previewImage.empty();
        	  previewImage.append('<img style="width:' + width + 'px;height:' + height + 'px;" src="' + baseJsUrl + 'AjaxUploader/upload_error' + sizeType + '.png">');
              dialogTips(response.msg, 5);
          }
        },
      onError: function() {
    	previewImage.empty();
    	previewImage.append('<img style="width:' + width + 'px;height:' + height + 'px;" src="' + baseJsUrl + 'AjaxUploader/upload_error' + sizeType + '.png">');
      }
	});
}