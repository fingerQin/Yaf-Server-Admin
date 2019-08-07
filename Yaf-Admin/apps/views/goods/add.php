{{include file="common/header.php"}}

<style type="text/css">
.spec-table .prods-area{background:#f8f8f8;padding:12px}
.spec-table .prods-area dl{*zoom:1;line-height:100%}
.spec-table .prods-area dl:after{display:table;line-height:0;content:"";clear:both}
.spec-table .prods-area dl dt,.goods-publish .goods-publish-form .prods-area dl dd{float:left;line-height:24px}
.spec-table .prods-area dl dt{text-align:right}
.spec-table .prods-area dl dd{*zoom:1;width:700px}
.spec-table .prods-area dl dd:after{display:table;line-height:0;content:"";clear:both}
.spec-table .prods-area .spec_item{float:left;width:100px}
.spec-table .prods-area .spec_item .spec_cbx,.goods-publish .goods-publish-form .prods-area .spec_item .spec_txt{float:left;margin-right:5px}
.spec-table .prods-area .spec_item .spec_cbx img,.goods-publish .goods-publish-form .prods-area .spec_item .spec_cbx .i-add{cursor:pointer}
.spec-table .prods-area .spec_item .spec_txt input.form-input{line-height:22px;height:22px;width:40px;display:none}
.spec-table .prods-area .spec_item .spec_txt span{cursor:pointer;display:inline-block;vertical-align:middle;color:#333}
.spec-table .prods-area .prods-table{width:100%;text-align:center;}
.spec-table .prods-area .prods-table th, .goods-publish-form .prods-area .prods-table td{border:1px solid #e1e1e1;line-height:24px;background:#fff;padding:0 10px}
.spec-table .prods-area .prods-table thead td{border-top-color:#aaa;white-space:nowrap;color:#333}
.spec-table .prods-area .prods-table tbody td{padding:2px 5px;white-space:nowrap}
.spec-table .prods-area .prods-table input.form-input{line-height:22px;height:22px;width:40px}
.spec-table .prods-area .line{border-bottom:1px dashed #999}
.spec-table .price-area .area-item{float:left;margin-right:10px}
.spec-table .price-area .area-item .item-cbx,.goods-publish .goods-publish-form .price-area .area-item .item-txt{float:left;margin-right:5px}
.spec-table .price-area .area-item .item-txt input.form-input{line-height:22px;height:22px}
.spec-table .price-area .area-item .item-txt span{cursor:pointer;display:inline-block;vertical-align:middle;color:#333}
.spec-table dl{margin: 5px;}
.goods_image {float:left; margin: 5px;}
.main .content .left-txt {width:20%}
#add_spec_img {cursor: pointer;}
.ke-toolbar {background-color:#f8f8f8;}
</style>

<div class="main">
    <form action="{{'Goods/add'|url}}" method="post" name="myform" id="goods-prods-form">
        <table class="content" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<th class="left-txt">商品名称：</th>
				<td><input type="text" name="goods_name" id="goods_name" size="60" class="form-control" value=""></td>
			</tr>
			<tr>
                <th class="left-txt">分类：</th>
                <td>
                    <select name="cat_id" id="cat_id" class="form-control">
                        <option value="-1">请选择分类</option>
                        {{foreach $cat_list as $cat}}
                        <option value="{{$cat.cat_id}}">{{$cat.cat_name}}</option>
                        {{foreach $cat.sub as $cat_two}}
                        <option value="{{$cat_two.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$cat_two.cat_name}}</option>
                        {{foreach $cat_two.sub as $cat_three}}
                        <option value="{{$cat_three.cat_id}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├─{{$cat_three.cat_name}}</option>
                        {{/foreach}}
                        {{/foreach}}
                        {{/foreach}}
                    </select>
                </td>
            </tr>
            <tr>
				<th class="left-txt">上下架状态：</th>
				<td>
				    <select name="marketable" id="marketable" class="form-control" style="width:120px;">
						<option value="1">上架</option>
						<option value="0">下架</option>
				    </select>
				</td>
			</tr>
            <tr>
                <th class="left-txt">排序值(值小排前):</th>
                <td><input type="text" name="listorder" id="listorder" size="5" class="form-control" style="width:120px;" value="0"></td>
            </tr>
            <tr>
                <th class="left-txt">商品相册：</th>
                <td>
                    <input type="hidden" name="goods_album[]" id="input_voucher1" value="" />
                    <div id="previewImage1" class="goods_image"></div>

                    <input type="hidden" name="goods_album[]" id="input_voucher2" value="" />
                    <div id="previewImage2" class="goods_image"></div>

                    <input type="hidden" name="goods_album[]" id="input_voucher3" value="" />
                    <div id="previewImage3" class="goods_image"></div>

                    <input type="hidden" name="goods_album[]" id="input_voucher4" value="" />
                    <div id="previewImage4" class="goods_image"></div>

                    <input type="hidden" name="goods_album[]" id="input_voucher5" value="" />
                    <div id="previewImage5" class="goods_image"></div>
                </td>
            </tr>

			<tr>
				<th class="left-txt">规格：</th>
				<td>
					<div class="spec-table" style="width:90%">
			            <div class="prods-area" id="spec-area">

			                <div id="plus_spec" style="margin-bottom: 10px;">
                                <img id="add_spec_img" style="padding:15px 0px;" src="{{'plus.png'|image}}" title="添加新规格" />
			                </div>
			                <div id="goods-prods-table">
			                    <!-- 单规格商品 start -->
			                    <div id="single-prods-form">
			                        <table class="prods-table m-t-10">
			                            <thead>
			                                <tr>
			                                    <td><span class="red">*</span>市场价</td>
			                                    <td><span class="red">*</span>销售价</td>
			                                    <td><span class="red">*</span>库存</td>
			                                    <td><span class="red">*</span>商家编码</td>
			                                </tr>
			                            </thead>
			                            <tbody>
			                                <tr>
			                                    <td><input type="text" class="form-input" name="products[single_product][market_price]" style="width:55px;" value="" placeholder="￥"> 金币</td>
			                                    <td><input type="text" class="form-input" name="products[single_product][sales_price]" style="width:55px;" value="" placeholder="￥"> 金币</td>
			                                    <td><input type="text" class="form-input" name="products[single_product][product_stock]" style="width:55px;" value="" placeholder="999"> </td>
			                                    <td><input type="text" class="form-input" name="products[single_product][sku_id]" placeholder="货品 SKU" value="" style="width:100px;"></td>
			                                </tr>
			                            </tbody>
			                        </table>
			                    </div>
			                    <!-- 是否是单货品：非多规格商品 -->
			                    <input type="hidden" name="is_single_product" value="1" id="is_single_product" />
			                    <!-- 单规格商品 end -->

			                </div>
			            </div>
			        </div>
				</td>
			</tr>

			<tr>
				<th class="left-txt">商品详情：</th>
                <td><textarea name="description" id="editor_id" style="width: 90%; height: 400px;" rows="5" cols="50"></textarea></td>
			</tr>
            <tr>
				<td></td>
				<td>
					<span><input class="btn btn-default" id="form-submit" type="submit" value="保存并提交"></span>
				</td>
			</tr>
		</table>

	</form>
</div>

<script charset="utf-8" src="{{'kindeditor/kindeditor-all.js'|js}}"></script>
<script charset="utf-8" src="{{'kindeditor/lang/zh-CN.js'|js}}"></script>
<script src="{{'AjaxUploader/uploadImage.js'|js}}"></script>

<script type="text/javascript">

var uploadUrl = '{{'Index/Upload'|url}}';
var baseJsUrl = '{{''|js}}';
var filUrl    = '{{$files_domain_name}}';
uploadImage(filUrl, baseJsUrl, 'previewImage1', 'input_voucher1', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage2', 'input_voucher2', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage3', 'input_voucher3', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage4', 'input_voucher4', 120, 120, uploadUrl);
uploadImage(filUrl, baseJsUrl, 'previewImage5', 'input_voucher5', 120, 120, uploadUrl);


    var editor;

    $(document).ready(function() {
        KindEditor.ready(function(K) {
            editor = K.create('#editor_id', {
                'items': [ 'source', '|', 'preview', 'template', 'code', '|',
                'justifyleft', 'justifycenter', 'justifyright',
                'clearhtml', 'selectall', 'removeformat', '|', 
                'formatblock', 'bold', 'italic', 'underline', 'strikethrough', '|', 'image',
                'flash', 'media', 'insertfile', 'table', 'baidumap', 'pagebreak',
                'anchor', 'link', 'unlink', 'fullscreen'],
                'cssPath' : '{{'kindeditor_goods.css'|css}}',
                'uploadJson' : '{{'Goods/upload'|url}}',
                'allowFileManager' : false,
                'urlType' : 'domain'
            });
        });

        // 添加新规格。
        $('#add_spec_img').click(function(){
            obj = $(this);
            layer.prompt({title: '添加新规格'}, function(val, index){
              layer.close(index);
              spec_item = createSpecTemplate(val);
              obj.before(spec_item);
            });
        });
      
        // 默认给添加新规格的按钮增加 Tips 提示。
        layer.tips('点我添加新的规格哟!', '#add_spec_img', {time: 5000});
    });

    /**
     * 创建一个规格模板。
     *
     * -- 方便快速创建规格。
     * 
     * @return string
     */
    function createSpecTemplate(specName) {
        // 第一步：判断规格名称是否重复。
        var guid = spec_guid();
        var spec_item = '<dl data-spec-key="' + specName + '"><dt><img class="del_spec_img" src="{{'del_spec.png'|image}}" title="删除规格" /> ' + specName + '</dt><dd class="cc"><div class="spec_item"><span class="spec_cbx"><input type="checkbox"></span><label class="spec_txt"><span>其他</span><input style="display: none;" disabled="" type="text" name="spec_val_alias[' + specName + ':::' + guid + ']" class="form-input" data-spec-val="' + guid + '" value="其他"></label></div></dd></dl>';
        return spec_item; 
    }

    /**
     * 生成一个唯一码。
     * @return string
     */
    function spec_guid() {
        function S4() {
            return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        }
        return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
    }

    !function (require) {
        var Form                 = require('Form'), Http = require('Http'), Feedback = require('Feedback');
        var moneyRule            = Form.getRegExpRule('money'),rateRule = /^(0|0\.[0-9]{1,2})$/;
        var goodsProdsTableCache = {};
        var $goodsProdsForm      = $('#goods-prods-form'),$wholesalePrices = $goodsProdsForm.find('#wholesale-prices');
        var $goodsProdsTable     = $('#goods-prods-table');

        function guid() {
            function S4() {
                return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
            }
            return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
        }

        function getSpecValueArrays(specStore) {
            var arr = [];
            for (var i = 0; i < specStore.length; i++) {
                var _arr = [];
                for (var n = 1; n < specStore[i].length; n++) {
                    _arr.push(specStore[i][n]);
                }
                arr.push(_arr);
            }
            return arr;
        }

        function enumSpecFunc(specValueArrays) {
            if (specValueArrays.length > 1) {
                var len1 = specValueArrays[0].length, len2 = specValueArrays[1].length, newArr = specValueArrays.slice(0), temp = [];
                for (var i = 0; i < len1; i++) {
                    for (var j = 0; j < len2; j++) {
                        temp.push([specValueArrays[0][i], specValueArrays[1][j]].join(','))
                    }
                }
                newArr.splice(0, 2, temp);
                return arguments.callee(newArr)
            }
            return specValueArrays[0];
        }

        function getProdTableTpl(selectedSpecInfo, cacheProdsTableData) {
            if (selectedSpecInfo.ids.length == 0) {
                return '';
            } else {
                var priceHeadBase = '<td><span class="red">*</span>市场价</td><td><span class="red">*</span>销售价</td><td><span class="red">*</span>库存</td>';
                var headBase = priceHeadBase+'<td><span class="red">*</span>商家编码</td>';
                //<td><span class="red">*</span>数量</td>
                var trBase = function (name) {
                    var sales_price   = '',
                        market_price  = '',
                        product_stock = '',
                        sku_id        = '',
                        productInfo   = {};
                    if (cacheProdsTableData && cacheProdsTableData.products && (name in cacheProdsTableData.products)) {
                        productInfo   = cacheProdsTableData.products[name];
                        market_price  = productInfo['market_price'];
                        product_stock = productInfo['product_stock'];
                        sales_price   = productInfo['sales_price'];
                        sku_id        = productInfo['sku_id'];
                    }
                    var
                    priceTdHtml  = '<td><input type="text" class="form-input" name="products['+name+ '][market_price]" style="width:50px;" value="' + market_price + '" placeholder="￥"/> 金币</td>';
                    priceTdHtml += '<td><input type="text" class="form-input" name="products['+name+ '][sales_price]" style="width:50px;" value="' + sales_price + '" placeholder="￥"/> 金币</td>';
                    priceTdHtml += '<td><input type="text" class="form-input" name="products['+name+ '][product_stock]" style="width:50px;" value="' + product_stock + '" placeholder="999"/> </td>';
                    return [priceTdHtml+'<td><input type="text" class="form-input" name="products[', '][sku_id]" placeholder="SKU" value="' + sku_id + '" style="width:100px;"/></td>'].join(name);
                };
                var headArr = [], trArr = [],
                    enumSpecId    = enumSpecFunc(getSpecValueArrays(selectedSpecInfo.ids)),
                    enumSpecLabel = enumSpecFunc(getSpecValueArrays(selectedSpecInfo.labels));
                for (var i = 0; i < selectedSpecInfo.ids.length; i++) {
                    headArr.push(['<td>', '</td>'].join(selectedSpecInfo.labels[i][0]));
                }
                for (var ii = 0; ii < enumSpecId.length; ii++) {
                    var prodsSpecIds = [], prodsSpecLabels = [];
                    var tr = [];
                    var enumSpecIdItemArr    = enumSpecId[ii].split(',');
                    var enumSpecLabelItemArr = enumSpecLabel[ii].split(',');
                    for (var n = 0; n < enumSpecIdItemArr.length; n++) {
                        prodsSpecIds.push([selectedSpecInfo.ids[n][0], enumSpecIdItemArr[n]].join(':::'));
                        prodsSpecLabels.push([selectedSpecInfo.labels[n][0], enumSpecLabelItemArr[n]].join(':::'));
                        tr.push(['<td>', '</td>'].join(enumSpecLabelItemArr[n]));
                    }
                    trArr.push(['<tr>', '</tr>'].join(tr.join('') + trBase(prodsSpecIds.join('|||'))));
                }
                return '<table class="prods-table m-t-10"><thead><tr>' + (headArr.join('') + headBase) + '</tr></thead><tbody>' + (trArr.join('')) + '</tbody></table>';
            }
        }

        function checkSpec2Prods() {
            var specLabelStore = [], specIdStore = [];
            $goodsProdsForm.find('.prods-area dl[data-spec-key]').each(function () {
                var specKey = $(this).data('spec-key');
                var hasChecked = false;
                //specLabelItemStore 纪录别名和描述  //specIdItemStore 规格键
                var specLabelItemStore = [specKey], specIdItemStore = [specKey];
                $(this).find('.spec_item').each(function () {
                    var isChecked = $(this).find('input[type=checkbox]').is(':checked');
                    var $specItemValLabel = $(this).find('.spec_txt span'), $specItemValInput = $(this).find('.spec_txt input.form-input');
                    if (isChecked) {
                        specLabelItemStore.push($specItemValInput.val().toString());
                        specIdItemStore.push($specItemValInput.data('spec-val').toString());
                        $specItemValLabel.hide();
                        $specItemValInput.attr('disabled',false).show();
                        if (!hasChecked)hasChecked = true;
                    } else {
                        $specItemValLabel.show();
                        $specItemValInput.attr('disabled',true).hide();
                    }
                });
                if (hasChecked) {
                    specLabelStore.push(specLabelItemStore);
                    specIdStore.push(specIdItemStore);
                }
            });
            return {
                labels: specLabelStore,
                ids: specIdStore
            };
        }

        function goodsProdsTableData($goodsProdsTable) {
            var serializeArray = [];
            $goodsProdsTable.find('[name]').each(function () {
                var name  = this.name;
                var value = $(this).val();
                serializeArray.push({
                    name: name,
                    value: value
                });
            });
            return Form.serializeObject(serializeArray);
        }

        // 规格值"其他"按钮 change 事件。
        $goodsProdsForm.on('change', '.prods-area dl[data-spec-key] input[type=checkbox],.price-area .item-cbx [type=checkbox]',function (event) {
            var selectedSpecInfo = checkSpec2Prods();
            var tableTpl = getProdTableTpl(selectedSpecInfo, goodsProdsTableData($goodsProdsTable));
            $goodsProdsTable.html(tableTpl);
            if (tableTpl) {
                // $singleProdsForm.hide().find('input').attr('disabled', true);
                // 多规格情况下。
            } else {
                // 单规格情况下显示单规格表格。
                $('#goods-prods-table').append('<table class="prods-table m-t-10"><thead><tr><td><span class="red">*</span>市场价</td><td><span class="red">*</span>销售价</td><td><span class="red">*</span>库存</td><td><span class="red">*</span>商家编码</td></tr></thead><tbody><tr><td><input type="text" class="form-input" name="products[single_product][market_price]" style="width:50px;" value="" placeholder="￥"> 元</td><td><input type="text" class="form-input" name="products[single_product][sales_price]" style="width:50px;" value="" placeholder="￥"> 元</td><td><input type="text" class="form-input" name="products[single_product][product_stock]" style="width:50px;" value="" placeholder="999"> </td><td><input type="text" class="form-input" name="products[single_product][sku_id]" placeholder="SKU" value="" style="width:100px;"></td></tr></tbody></table><input type="hidden" name="is_single_product" value="1" id="is_single_product" />');
                // $singleProdsForm.show().find('input').attr('disabled', false);
            }
            var $specKey = $(this).parents('[data-spec-key]:first');
            if($specKey.find('[type=checkbox]').size()==$specKey.find('[type=checkbox]:checked').size()){
                var _guid = guid();
                $specKey.find('dd.cc').append('<div class="spec_item"><span class="spec_cbx"><input type="checkbox"></span><label class="spec_txt"><span>其他</span><input style="display: none;" disabled type="text" name="spec_val_alias['+([$specKey.data('spec-key'),_guid].join(':::'))+']" class="form-input" data-spec-val="'+_guid+'" value="其他" style="display: inline-block;"></label></div>');
            }
        });
        
        // 规格值发生改变重置表格对应值。
        var keyUpSt;
        $goodsProdsForm.on('keyup', '[name^=spec_val_alias]',function() {
            clearTimeout(keyUpSt);
            keyUpSt = setTimeout(function(){
                var selectedSpecInfo = checkSpec2Prods();
                var tableTpl = getProdTableTpl(selectedSpecInfo, goodsProdsTableData($goodsProdsTable));
                $goodsProdsTable.html(tableTpl);
            },50);
        });

        // 删除规格。
        var specNameChange;
        $goodsProdsForm.on('click', '.del_spec_img', function(){
            obj = $(this);
            layer.confirm('您确定要删除该规格吗？', {
                btn: ['确定','取消'] //按钮
            }, function(index){
                obj.parent().parent().remove();
                layer.close(index);
                // 此时规格名发生变化。要进行相应的变动。
                clearTimeout(specNameChange);
                specNameKeyUp = setTimeout(function(){
                    var selectedSpecInfo = checkSpec2Prods();
                    var tableTpl = getProdTableTpl(selectedSpecInfo, goodsProdsTableData($goodsProdsTable));
                    $goodsProdsTable.html(tableTpl);
                },50);
            }, function(index){
                layer.close(index);
            });
        });

        // 表单提交。
        $goodsProdsForm.submit(function () {
            editor.sync();
            var result = Form.serializeObject($goodsProdsForm.serializeArray());
            var errmsg;

            if (!errmsg) {
                // 没有多规格的情况。
                if ($.isEmptyObject(result.products)) {
                    if (result.market_price == '' || !moneyRule.test(result.market_price)) {
                        errmsg = '市场价填写有误';
                    } else if (result.sales_price == '' || !moneyRule.test(result.sales_price)) {
                        errmsg = '销售价填写有误';
                    } else if (result.product_stock == '') {
                        errmsg = '库存填写有误';
                    } else if (result.sku_id == '') {
                        errmsg = '商家编码必填';
                    }
                } else {
                    // 多规格情况。
                    for (var key in result.products) {
                        if (result.products[key].market_price == '' || !moneyRule.test(result.products[key].market_price)) {
                            errmsg = '市场价填写有误';
                        } else if (result.products[key].sales_price == '' || !moneyRule.test(result.products[key].sales_price)) {
                            errmsg = '销售价填写有误';
                        } else if (result.products[key].product_stock == '') {
                            errmsg = '库存必须填写';
                        } else if (result.products[key].sku_id == '') {
                            errmsg = '商家编码必填';
                        }
                        if(errmsg)break;
                    }
                }
            }

            if (errmsg) {
                layer.tips(errmsg, '#form-submit');
                return false;
            }

            // 根据是否单货品进行获取具体的信息。
            singleProduct = $('#is_single_product').val();
            if (!singleProduct) {
                var products = {},specVals = {};
                var temp = [];
                for (var specKeyStr in result.products) {
                    var specKeys = specKeyStr.split('|||');
                    var arr = [];
                    for (var i = 0; i < specKeys.length; i++) {
                        var specKey = specKeys[i].split(':::')[0], specVal = specKeys[i].split(':::')[1];
                        if(result.spec_val_alias[specKeys[i]]){
                            specVal = result.spec_val_alias[specKeys[i]];
                        }
                        arr.push([specKey,specVal].join(':::'));
                    }
                    var productSpecKey = arr.join('|||');
                    if (temp.indexOf(productSpecKey) >-1 ) {
                        layer.tips('规格' + productSpecKey + '出现了重复，请设置不同的规格值名称！', '#form-submit');
                        return false;
                    }
                    temp.push(productSpecKey);
                    products[productSpecKey] = result.products[specKeyStr];
                }
                for (var aliasStr in result.spec_val_alias) {
                    var key = aliasStr.split(':::')[0], val = aliasStr.split(':::')[1];
                    if (!specVals[key]){
                        specVals[key] = [result.spec_val_alias[aliasStr]];
                    } else {
                        specVals[key].push(result.spec_val_alias[aliasStr]);
                    }
                }
                result.spec_val = specVals;
                result.products = products;
                delete result.spec_val_alias;
                delete result.market_price;
                delete result.sales_price;
                delete result.sku_id;
                delete result.product_stock;
            } else {
                result = {
                    goods_id:result.goods_id,
                    spec_val:[0],
                    products:result.products,
                    goods_album:result.goods_album
                };
            }

            editor.sync();

            result.goods_name  = $('#goods_name').val();
            result.cat_id 	   = $('#cat_id').val();
            result.listorder   = $('#listorder').val();
            result.description = $('#editor_id').val();
            result.marketable  = $('#marketable').val();

            $.ajax({
                url : '',
                data : result,
                dataType : 'json',
                success : function (response) {
                    if (response.code == 200) {
                        parent.location.reload();
                    } else {
                        layer.tips(response.msg, '#form-submit');
                    }
                }
            });

            return false;
        });

    }(seajs.require);
</script>

{{include file="common/footer.php"}}