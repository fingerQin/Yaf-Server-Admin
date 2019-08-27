<?php
/**
 * 商品相关业务封装。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Services\Mall;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Models\Category;
use Models\MallGoods;
use Models\MallGoodsImage;
use Models\MallProduct;
use Services\AbstractBase;

class Goods extends AbstractBase
{
    /**
     * 获取商品列表。
     *
     * @param  int     $updown        上下架状态。-1不限、1上架、0下架。
     * @param  string  $goodsName     商品名称。
     * @param  int     $catId         分类ID。
     * @param  int     $startPrice    价格最小值。
     * @param  int     $endPrice      价格最大值。
     * @param  int     $isDeleteShow  是否显示已删除的商品。0否、1是。
     * @param  int     $page          当前页码。
     * @param  int     $count         每页显示条数。
     * @return array
     */
    public static function list($updown = -1, $goodsName = '', $catId = -1, $startPrice = '', $endPrice = '', $isDeleteShow = 0, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM mall_goods ';
        $columns   = ' * ';
        $where     = ' WHERE 1 ';
        $params    = [];
        if (!$isDeleteShow) {
            $where .= ' AND status = :status ';
            $params[':status'] = MallGoods::STATUS_YES;
        }
        if (strlen($goodsName) > 0) {
            $where .= ' AND goods_name LIKE :goods_name ';
            $params[':goods_name'] = "%{$goodsName}%";
        }
        if ($catId != -1) {
            $CategoryModel = new Category();
            $catInfo = $CategoryModel->fetchOne([], ['cat_id' => $catId, 'status' => 1]);
            if (empty($catInfo)) {
                $where .= ' AND cat_code = :cat_code ';
                $params[':cat_code'] = '';
            } else {
                $where .= ' AND cat_code LIKE :cat_code ';
                $catCodePrefix = \Services\System\Category::getCatCodePrefix($catInfo['cat_code'], $catInfo['lv']);
                $params[':cat_code'] = "{$catCodePrefix}%";
            }
        }
        if (strlen($startPrice) > 0) {
            if (!Validator::is_integer($startPrice)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询价格必须是整数');
            }
            $where .= ' AND min_price >= :start_price ';
            $params[':start_price'] = $startPrice;
        }
        if (strlen($endPrice) > 0) {
            if (!Validator::is_integer($endPrice)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询价格必须是整数');
            }
            $where .= ' AND max_price <= :end_price ';
            $params[':end_price'] = $endPrice;
        }
        if ($updown != -1) {
            $where .= ' AND marketable = :marketable ';
            $params[':marketable'] = $updown;
        }
        $orderBy   = ' ORDER BY goodsid DESC ';
        $sql       = "SELECT COUNT(1) AS count {$fromTable} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} {$fromTable} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取商品详情。
     *
     * @param  int  $goodsId  商品ID。
     * @return array
     */
    public static function detail($goodsId)
    {
        $GoodsModel = new MallGoods();
        $columns = [
            'goodsid', 'goods_name', 'cat_code', 'min_market_price', 'max_market_price', 'min_price',
            'max_price', 'goods_img', 'listorder', 'spec_val_json', 'marketable', 'description'
        ];
        $goodsDetail = $GoodsModel->fetchOne($columns, ['goodsid' => $goodsId, 'status' => MallGoods::STATUS_YES]);
        if (empty($goodsDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品不存在');
        }
        // 商品相册。
        $where = [
            'goodsid' => $goodsId,
            'status'  => MallGoodsImage::STATUS_YES
        ];
        $GoodsImageModel = new MallGoodsImage();
        $goodsImages     = $GoodsImageModel->fetchAll(['image_url'], $where, 5, 'listsort ASC,imageid ASC');
        $goodsImageList  = [];
        if (!empty($goodsImages)) {
            foreach ($goodsImages as $image) {
                $goodsImageList[] = $image['image_url'];
            }
        }
        $goodsDetail['products']    = self::getGoodsProducts($goodsId);
        $goodsDetail['spec_val']    = json_decode($goodsDetail['spec_val_json'], true);
        $goodsDetail['goods_image'] = $goodsImageList;
        unset($goodsDetail['spec_val_json'], $goodsImages, $GoodsImageModel, $GoodsModel);
        return $goodsDetail;
    }

    /**
     * 获取商品货品数据。
     * @param  int   $goodsId  商品ID。
     * @return array
     */
    protected static function getGoodsProducts($goodsId)
    {
        // 商品货品列表。
        $ProductModel = new MallProduct();
        $columns      = ['productid', 'market_price', 'sales_price', 'stock', 'spec_val', 'skuid'];
        $productList  = $ProductModel->fetchAll($columns, [
            'goodsid' => $goodsId, 
            'status'  => MallProduct::STATUS_YES
        ], 0, 'productid ASC');
        if (empty($productList)) {
            return [];
        }
        $products = [];
        foreach ($productList as $product) {
            if (strlen($product['spec_val']) === 0) { // 单规格。
                $product['arr_spec_val']    = [];
                $products['single_product'] = $product; // single_product 特殊标识符。
            } else {
                $specValSplit  = explode('|||', $product['spec_val']);
                $specKeyValArr = [];
                foreach ($specValSplit as $specVal) {
                    $_temp = explode(':::', $specVal);
                    $specKeyValArr[$_temp[0]] = $_temp[1];
                }
                $product['arr_spec_val']        = $specKeyValArr; // 将货品对应的规格字符串转换为数组返回。
                $products[$product['spec_val']] = $product;
            }
        }
        unset($productList, $product, $ProductModel);
        return $products;
    }

    /**
     * 添加商品。
     * -- Example start --
     * $data = [
     *      'user_id'     => '添加商品的用户ID',
     *      'goods_name'  => '商品名称',
     *      'cat_id'      => '系统分类ID',
     *      'listorder'   => '排序值。小到大排列。',
     *      'description' => '商品详情。',
     *      'spec_val'    => '商品规格',
     *      'products'    => '库存与价格',
     *      'goods_album' => '商品相册',
     *      'marketable'  => '产品上下架状态：1-上架、0-下架',
     * ];
     *
     * spec_val = [
     *      '颜色' => ['银色', '黑色'],
     *      '尺寸' => ['35', '38']
     * ];
     *
     * // 注： products 中对应的键存在 single_product 则认为是单规格商品。
     * products = [
     *      '颜色:::银色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69384726212'],
     *      '颜色:::黑色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69384726212'],
     *      '颜色:::银色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69384726212'],
     *      '颜色:::黑色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69384726212'],
     * ];
     *
     * # 最多五张图片。第一张图片会更新到商品主图。
     * goods_album = [
     *      'images/voucher/20160401/56fe70362ef7e.jpg',
     *      'images/voucher/20160401/56fe705fd37a2.jpg',
     *      'images/voucher/20160401/56fe710513c9e.jpg',
     *      'images/voucher/20160402/56fea2043dc01.jpg',
     *      'images/voucher/20160402/56fea3f18677d.jpg'
     * ];
     *
     * -- Example end --
     *
     * @param  array  $data 商品数据。
     * @return void
     */
    public static function add($data)
    {
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '请认真添加商品');
        }
        if (!isset($data['goods_name']) || !Validator::is_len($data['goods_name'], 1, 100, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品名称必须1~100个字符');
        }
        if (!isset($data['description']) || !Validator::is_len($data['description'], 1, 10000, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品详情长度必须1~10000个字符');
        }
        if (!isset($data['spec_val']) || !is_array($data['spec_val'])) {
            YCore::exception(STATUS_SERVER_ERROR, '商品规格有误');
        }
        if (!isset($data['products']) || !is_array($data['products']) || empty($data['products']) || count($data['products']) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '货品数据有误');
        }
        if (!isset($data['goods_album']) || !is_array($data['goods_album'])) {
            YCore::exception(STATUS_SERVER_ERROR, '商品图片必须上传');
        }
        $albumCount = count($data['goods_album']);
        if ($albumCount != 5) {
            YCore::exception(STATUS_SERVER_ERROR, '商品相册数量必须5张');
        }
        // 初始化市场价与销售价的最大最小值。
        $minMarketPrice = 0;
        $maxMarketPrice = 0;
        $minPrice       = 0;
        $maxPrice       = 0;
        // 判断是否单规格商品以及验证值是否正确。
        if (array_key_exists('single_product', $data['products'])) {
            if (!Validator::is_integer($data['products']['single_product']['product_stock']) || !Validator::is_number_between($data['products']['single_product']['product_stock'], 0, 10000)) {
                YCore::exception(STATUS_SERVER_ERROR, '库存必须0~10000之间');
            }
            if (!isset($data['products']['single_product']['market_price']) || !Validator::is_number_between($data['products']['single_product']['market_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '市场价必须0.01~1000000之间');
            }
            if (!isset($data['products']['single_product']['sales_price']) || !Validator::is_number_between($data['products']['single_product']['sales_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '销售价必须0.01~1000000之间');
            }
            $data['spec_val'] = [];
            $minMarketPrice   = $data['products']['single_product']['market_price'];
            $maxMarketPrice   = $data['products']['single_product']['market_price'];
            $minPrice         = $data['products']['single_product']['sales_price'];
            $maxPrice         = $data['products']['single_product']['sales_price'];
        } else { // 多规格。
            foreach ($data['products'] as $pro) {
                if ($minMarketPrice == 0 || $minMarketPrice > $pro['market_price']) {
                    $minMarketPrice = $pro['market_price'];
                }
                if ($maxMarketPrice == 0 || $maxMarketPrice < $pro['market_price']) {
                    $maxMarketPrice = $pro['market_price'];
                }
                if ($minPrice == 0 || $minPrice > $pro['sales_price']) {
                    $minPrice = $pro['sales_price'];
                }
                if ($maxPrice == 0 || $maxPrice < $pro['sales_price']) {
                    $maxPrice = $pro['sales_price'];
                }
            }
            self::checkGoodsSpecAndProduct($data['spec_val'], $data['products']);
        }
        $CatModel = new Category();
        $catInfo  = $CatModel->fetchOne([], ['cat_id' => $data['cat_id'], 'status' => Category::STATUS_YES]);
        if (empty($catInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        // 商品相册第一张作为商品主图。
        $goodsImg   = (isset($data['goods_album'][0])) ? $data['goods_album'][0] : '';
        $datetime   = date('Y-m-d H:i:s', time());
        $insertData = [
            'goods_name'       => $data['goods_name'],
            'cat_code'         => $catInfo['cat_code'],
            'goods_img'        => $goodsImg,
            'marketable'       => $data['marketable'] ? 1 : 0,
            'marketable_time'  => $datetime,
            'status'           => MallGoods::STATUS_YES,
            'min_market_price' => $minMarketPrice,
            'max_market_price' => $maxMarketPrice,
            'min_price'        => $minPrice,
            'max_price'        => $maxPrice,
            'spec_val_json'    => json_encode($data['spec_val']),
            'description'      => $data['description'],
            'listorder'        => $data['listorder'],
            'c_time'           => $datetime,
            'c_by'             => $data['user_id']
        ];
        $GoodsModel = new MallGoods();
        Db::beginTransaction();
        $goodsId = $GoodsModel->insert($insertData);
        if ($goodsId <= 0) {
            Db::rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '商品添加失败');
        }
        try {
            self::setGoodsProduct($data['user_id'], $goodsId, $data['products']);
            self::setGoodsImage($data['user_id'], $goodsId, $data['goods_album']);
        } catch (\Exception $e) {
            Db::rollBack();
            YCore::exception($e->getCode(), $e->getMessage());
        }
        Db::commit();
    }

    /**
     * 编辑商品。
     * -- Example start --
     * $data = [
     *      'user_id'     => '添加商品的用户ID',
     *      'goods_id'    => '被编辑商品的ID',
     *      'goods_name'  => '商品名称',
     *      'cat_id'      => '系统分类ID',
     *      'listorder'   => '排序值。小到大排列。',
     *      'description' => '商品详情。',
     *      'spec_val'    => '商品规格',
     *      'products'    => '库存与价格',
     *      'goods_album' => '商品相册',
     *      'marketable'  => '产品上下架状态：1-上架、0-下架',
     * ];
     *
     * spec_val = [
     *      '颜色' => ['银色', '黑色'],
     *      '尺寸' => ['35', '38']
     * ];
     *
     * // 注： products 中对应的键存在 single_product 则认为是单规格商品。
     * products = [
     *      '颜色:::银色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69832726726'],
     *      '颜色:::黑色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69832726726'],
     *      '颜色:::银色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69832726726'],
     *      '颜色:::黑色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '69832726726'],
     * ];
     *
     * # 最多五张图片。第一张图片会更新到商品主图。
     * goods_album = [
     *      'images/voucher/20160401/56fe70362ef7e.jpg',
     *      'images/voucher/20160401/56fe705fd37a2.jpg',
     *      'images/voucher/20160401/56fe710513c9e.jpg',
     *      'images/voucher/20160402/56fea2043dc01.jpg',
     *      'images/voucher/20160402/56fea3f18677d.jpg'
     * ];
     *
     * -- Example end --
     *
     * @param  array $data 商品数据。
     * @return void
     */
    public static function edit($data)
    {
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '请认真添加商品');
        }
        if (!isset($data['goods_name']) || !Validator::is_len($data['goods_name'], 1, 100, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品名称必须1~100个字符');
        }
        if (!isset($data['description']) || !Validator::is_len($data['description'], 1, 10000, true)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品详情长度必须1~10000个字符');
        }
        if (!isset($data['spec_val']) || !is_array($data['spec_val'])) {
            YCore::exception(STATUS_SERVER_ERROR, '商品规格有误');
        }
        if (!isset($data['products']) || !is_array($data['products']) || empty($data['products']) || count($data['products']) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '货品数据有误');
        }
        if (!isset($data['goods_album']) || !is_array($data['goods_album'])) {
            YCore::exception(STATUS_SERVER_ERROR, '商品图片必须上传');
        }
        $albumCount = count($data['goods_album']);
        if ($albumCount != 5) {
            YCore::exception(STATUS_SERVER_ERROR, '商品相册数量必须5张');
        }
        // 初始化市场价与销售价的最大最小值。
        $minMarketPrice = 0;
        $maxMarketPrice = 0;
        $minPrice       = 0;
        $maxPrice       = 0;
        // 判断是否单规格商品以及验证值是否正确。
        if (array_key_exists('single_product', $data['products'])) {
            if (!Validator::is_integer($data['products']['single_product']['product_stock']) || !Validator::is_number_between($data['products']['single_product']['product_stock'], 0, 10000)) {
                YCore::exception(STATUS_SERVER_ERROR, '库存必须0~10000之间');
            }
            if (!isset($data['products']['single_product']['market_price']) || !Validator::is_number_between($data['products']['single_product']['market_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '市场价必须0.01~1000000之间');
            }
            if (!isset($data['products']['single_product']['sales_price']) || !Validator::is_number_between($data['products']['single_product']['sales_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '销售价必须0.01~1000000之间');
            }
            $data['spec_val'] = [];
            $minMarketPrice   = $data['products']['single_product']['market_price'];
            $maxMarketPrice   = $data['products']['single_product']['market_price'];
            $minPrice         = $data['products']['single_product']['sales_price'];
            $maxPrice         = $data['products']['single_product']['sales_price'];
        } else { // 多规格。
            foreach ($data['products'] as $pro) {
                if ($minMarketPrice == 0 || $minMarketPrice > $pro['market_price']) {
                    $minMarketPrice = $pro['market_price'];
                }
                if ($maxMarketPrice == 0 || $maxMarketPrice < $pro['market_price']) {
                    $maxMarketPrice = $pro['market_price'];
                }
                if ($minPrice == 0 || $minPrice > $pro['sales_price']) {
                    $minPrice = $pro['sales_price'];
                }
                if ($maxPrice == 0 || $maxPrice < $pro['sales_price']) {
                    $maxPrice = $pro['sales_price'];
                }
            }
            self::checkGoodsSpecAndProduct($data['spec_val'], $data['products']);
        }
        $CatModel = new Category();
        $catInfo  = $CatModel->fetchOne([], ['cat_id' => $data['cat_id'], 'status' => MallGoods::STATUS_YES]);
        if (empty($catInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        $GoodsModel = new MallGoods();
        $goodsInfo  = $GoodsModel->fetchOne([], ['goodsid' => $data['goods_id'], 'status' => MallGoods::STATUS_YES]);
        if (empty($goodsInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品不存在或已经删除');
        }
        // 商品相册第一张作为商品主图。
        $goodsImg = (isset($data['goods_album'][0])) ? $data['goods_album'][0] : '';
        $datetime = date('Y-m-d H:i:s', time());
        $updata   = [
            'goods_name'       => $data['goods_name'],
            'cat_code'         => $catInfo['cat_code'],
            'goods_img'        => $goodsImg,
            'min_market_price' => $minMarketPrice,
            'max_market_price' => $maxMarketPrice,
            'marketable'       => $data['marketable'] ? 1 : 0,
            'marketable_time'  => $datetime,
            'min_price'        => $minPrice,
            'max_price'        => $maxPrice,
            'spec_val_json'    => json_encode($data['spec_val']),
            'description'      => $data['description'],
            'listorder'        => $data['listorder'],
            'u_time'           => date('Y-m-d H:i:s', time()),
            'u_by'             => $data['user_id']
        ];
        $where = [
            'goodsid' => $data['goods_id'],
            'status'  => MallGoods::STATUS_YES
        ];
        Db::beginTransaction();
        $ok = $GoodsModel->update($updata, $where);
        if (!$ok) {
            Db::rollBack();
            YCore::exception(STATUS_SERVER_ERROR, '商品保存失败');
        }
        try {
            self::setGoodsProduct($data['user_id'], $data['goods_id'], $data['products']);
            self::setGoodsImage($data['user_id'], $data['goods_id'], $data['goods_album']);
        } catch (\Exception $e) {
            Db::rollBack();
            YCore::exception($e->getCode(), $e->getMessage());
        }
        Db::commit();
    }

    /**
     * 删除商品。
     *
     * @param  int  $userid   用户ID。
     * @param  int  $goodsId  商品ID。
     * @return void
     */
    public static function delete($userid, $goodsId)
    {
        $GoodsModel = new MallGoods();
        $where = [
            'goodsid' => $goodsId,
            'status'  => MallGoods::STATUS_YES
        ];
        $goodsInfo = $GoodsModel->fetchOne([], $where);
        if (empty($goodsInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品不存在或已经删除');
        }
        $data = [
            'status' => MallGoods::STATUS_DELETED,
            'u_by'   => $userid,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $GoodsModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '保存失败');
        }
    }

    /**
     * 商品上下架。
     *
     * @param  int  $userid   用户ID。
     * @param  int  $goodsId  商品ID。
     * @param  int  $updown   上下架状态。1上架、0下架。
     * @return void
     */
    public static function updownGoods($userid, $goodsId, $updown)
    {
        $GoodsModel = new MallGoods();
        $where = [
            'goodsid' => $goodsId,
            'status'  => MallGoods::STATUS_YES
        ];
        $goodsDetail = $GoodsModel->fetchOne([], $where);
        if (empty($goodsDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品不存在');
        }
        $data = [
            'u_by'            => $userid,
            'u_time'          => date('Y-m-d H:i:s', time()),
            'marketable'      => $updown ? 1 : 0,
            'marketable_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $GoodsModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试!');
        }
    }

    /**
     * 获取商品库存总数。
     *
     * @param  int  $goodsId 商品ID。
     * @return int
     */
    protected static function getGoodsStock($goodsId)
    {
        $sql = 'SELECT SUM(stock) AS stock FROM mall_product WHERE goodsid = :goodsid AND status = :status';
        $params = [
            ':goodsid' => $goodsId,
            ':status'  => MallProduct::STATUS_YES
        ];
        $data = Db::one($sql, $params);
        return $data ? $data['stock'] : 0;
    }

    /**
     * 设置商品的货品数据。
     * -- Example start --
     * // 注： products 中对应的键存在 single_product 则认为是单规格商品。
     * $products = [
     *      '颜色:::银色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '6948271616'],
     *      '颜色:::黑色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '6948271616'],
     *      '颜色:::银色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '6948271616'],
     *      '颜色:::黑色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'product_stock' => '999', 'sku_id' => '6948271616'],
     * ];
     * -- Example end --
     *
     * @param  int   $userid   用户ID。如果此程序放在管理后台就是管理员ID。如果是有商家中心就是商家账号ID。
     * @param  int   $goodsId  商品ID。
     * @param  array $products 货品数据。
     * @return void
     */
    protected static function setGoodsProduct($userid, $goodsId, array $products = [])
    {
        // [1] 得到修改前的货品数据。
        $sql    = 'SELECT productid,spec_val FROM mall_product WHERE goodsid = :goodsid AND status = :status';
        $params = [
            ':goodsid' => $goodsId,
            ':status'  => MallProduct::STATUS_YES
        ];
        $oldProductResult = Db::all($sql, $params);
        $oldProductList   = [];
        foreach ($oldProductResult as $op) {
            $oldProductList[$op['spec_val']] = $op['productid'];
        }
        $existsOldProductIds = [];
        // [2] 循环处理货品。
        foreach ($products as $specVal => $pro) {
            // [2.1] 参数判断。
            if (!Validator::is_integer($pro['product_stock']) || !Validator::is_number_between($pro['product_stock'], 0, 10000)) {
                YCore::exception(STATUS_SERVER_ERROR, '库存必须0~10000之间');
            }
            if (!Validator::is_number_between($pro['sales_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '销售价必须0.01~1000000之间');
            }
            if (!Validator::is_number_between($pro['market_price'], 0.01, 1000000)) {
                YCore::exception(STATUS_SERVER_ERROR, '市场价必须0.01~1000000之间');
            }
            // [2.2] 货品存在与否判断。
            $ProductModel = new MallProduct();
            if (array_key_exists($specVal, $oldProductList)) { // 存在，则只作更新。
                $productId = $oldProductList[$specVal];
                $where = [
                    'productid' => $productId,
                    'status'    => MallProduct::STATUS_YES
                ];
                $data = [
                    'market_price' => $pro['market_price'],
                    'sales_price'  => $pro['sales_price'],
                    'stock'        => $pro['product_stock'],
                    'u_by'         => $userid,
                    'u_time'       => date('Y-m-d H:i:s', time()),
                    'skuid'        => $pro['sku_id']
                ];
                $ok = $ProductModel->update($data, $where);
                if (!$ok) {
                    YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
                }
                $existsOldProductIds[] = $productId;
            } else { // 添加。
                $specVal = ($specVal == 'single_product') ? '' : $specVal;
                $data = [
                    'spec_val'     => $specVal,
                    'status'       => MallProduct::STATUS_YES,
                    'market_price' => $pro['market_price'],
                    'sales_price'  => $pro['sales_price'],
                    'stock'        => $pro['product_stock'],
                    'c_by'         => $userid,
                    'c_time'       => date('Y-m-d H:i:s', time()),
                    'goodsid'      => $goodsId,
                    'skuid'        => $pro['sku_id']
                ];
                $ok = $ProductModel->insert($data);
                if (!$ok) {
                    YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
                }
            }
        }
        // [3] 将不在新货品中的旧货品删掉。
        foreach ($oldProductList as $productId) {
            if (in_array($productId, $existsOldProductIds)) {
                continue;
            }
            $where = [
                'productid' => $productId
            ];
            $updateData = [
                'status' => MallProduct::STATUS_DELETED,
                'u_by'   => $userid,
                'u_time' => date('Y-m-d H:i:s', time())
            ];
            $ok = $ProductModel->update($updateData, $where);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
            }
        }
    }

    /**
     * 验证商品规格与货品规格是否匹配。
     * -- Example start --
     * $specVal = [
     *      '颜色' => ['银色', '黑色'],
     *      '尺寸' => ['35', '38']
     * ];
     *
     * $products = [
     *      '颜色:::银色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'stock' => '999'],
     *      '颜色:::黑色|||尺寸:::35' => ['market_price' => 129, 'sales_price' => 99, 'stock' => '999'],
     *      '颜色:::银色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'stock' => '999'],
     *      '颜色:::黑色|||尺寸:::38' => ['market_price' => 129, 'sales_price' => 99, 'stock' => '999'],
     * ];
     * -- Example end --
     *
     * @param  array $spec       商品规格数据。
     * @param  array $products   货品数据。
     * @return void
     */
    protected static function checkGoodsSpecAndProduct(array $spec = [], array $products = [])
    {
        if (empty($spec) || empty($products)) { // 此种情况认为是没有规格的商品。
            return;
        }
        $goodsSpecCount = count($spec);
        foreach ($products as $specVal => $pro) {
            $keyVal    = explode('|||', $specVal);
            $specCount = count($keyVal); // 得到货品规格中的规格对数量。如果这个数据与实际的商品规格数量不对应。说明有误。
            if ($goodsSpecCount != $specCount) {
                YCore::exception(STATUS_SERVER_ERROR, '商品规格设置有误');
            }
            if (empty($keyVal)) {
                YCore::exception(STATUS_SERVER_ERROR, '商品规格设置有误');
            }
            foreach ($keyVal as $key => $val) {
                $s_v = explode(':::', $val);
                if (count($s_v) != 2) {
                    YCore::exception(STATUS_SERVER_ERROR, '商品规格设置有误');
                }
                if (!array_key_exists($s_v[0], $spec)) { // $s_v[0] 是规格名称。 $s_v[1] 是规格值。
                    YCore::exception(STATUS_SERVER_ERROR, '商品规格设置有误');
                }
                if (!in_array($s_v[1], $spec[$s_v[0]])) {
                    YCore::exception(STATUS_SERVER_ERROR, '商品规格设置有误');
                }
            }
        }
    }

    /**
     * 设置商品相册。
     *
     * @param  int    $userid   添加相册的用户ID。
     * @param  int    $goodsId  商品ID。
     * @param  array  $album    相册。
     * @return void
     */
    protected static function setGoodsImage($userid, $goodsId, $album)
    {
        if (empty($album)) {
            return true;
        }
        $datetime   = date('Y-m-d H:i:s', time());
        $ImageModel = new MallGoodsImage();
        // [1] 查找该商品原相册图片。
        $where = [
            'goodsid' => $goodsId,
            'status'  => MallGoodsImage::STATUS_YES
        ];
        $columns   = ['imageid', 'image_url'];
        $oldImage  = $ImageModel->fetchAll($columns, $where);
        $_oldImage = [];
        foreach ($oldImage as $item) {
            $_oldImage[$item['image_url']] = $item['imageid'];
        }
        $oldImage = $_oldImage;
        // [2] 判断新入库的图片是否已经存在，已经存在则不做任何修改。
        // 如果旧图片在新图片中不存在，则要进行删除。
        $existsOldImageId = [];
        foreach ($album as $listsort => $imageUrl) {
            if (!empty($oldImage) && array_key_exists($imageUrl, $oldImage)) { // 存在。
                $existsOldImageId[] = $oldImage[$imageUrl];
                $updata = [
                    'listsort' => $listsort,
                    'u_time'   => $datetime,
                    'u_by'     => $userid
                ];
                $status = $ImageModel->update($updata, ['imageid' => $oldImage[$imageUrl]]);
                if (!$status) {
                    YCore::exception(STATUS_SERVER_ERROR, '相册图片保存失败');
                }
            } else { // 不存在。
                $insertData = [
                    'goodsid'   => $goodsId,
                    'image_url' => $imageUrl,
                    'status'    => MallGoodsImage::STATUS_YES,
                    'listsort'  => $listsort,
                    'c_time'    => $datetime,
                    'c_by'      => $userid
                ];
                $id = $ImageModel->insert($insertData);
                if (!$id) {
                    YCore::exception(STATUS_SERVER_ERROR, '相册图片保存失败');
                }
            }
        }
        // [3] 得到不在新图片中的旧图片ID。
        $notExistImageId = [];
        foreach ($oldImage as $imageId) {
            if (!in_array($imageId, $existsOldImageId)) {
                $notExistImageId[] = $imageId;
            }
        }
        // [4] 删除不在新图片中的旧图片ID对应的图片。
        if (!empty($notExistImageId)) {
            $where = [
                'imageid' => ['IN', $notExistImageId]
            ];
            $whereInfo = $ImageModel->parseWhereCondition($where);
            $sql       = "UPDATE mall_goods_image SET `status` = :status, u_by = :u_by, "
                       . "u_time = :u_time WHERE {$whereInfo['where']}";
            $params    = $whereInfo['params'];
            $params[':status'] = MallGoodsImage::STATUS_DELETED;
            $params[':u_by']   = $userid;
            $params[':u_time'] = $datetime;
            $ok = Db::execute($sql, $params);
            if (!$ok) {
                YCore::exception(STATUS_SERVER_ERROR, '相册图片保存失败');
            }
        }
    }

    /**
     * 扣减货品库存。
     * -- 1、购买商品成功通过此方法扣减库存。
     *
     * @param  int  $productId  货品ID。
     * @param  int  $stock      扣减的库存。
     * @return void
     */
    public static function deductionProductStock($productId, $stock)
    {
        $stock     = intval($stock); // 由于 stock = stock - :stock 使用PDO不支持。所以，直接写在SQL里面要进行强制类型转换避免注入。
        $sql       = "UPDATE mall_product SET stock = stock - {$stock} WHERE productid = :productid AND stock >= :stock";
        $params    = [
            ':stock'     => $stock,
            ':productid' => $productId
        ];
        $ok = Db::execute($sql, $params);
        if (!$ok) {
            YCore::exception(STATUS_SERVER_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 还原货品库存。
     * -- 1、订单关闭、取消需要把库存还原回来。
     *
     * @param  int  $productId  货品ID。
     * @param  int  $stock      扣减的库存。
     * @return void
     */
    public static function restoreProductStock($productId, $stock)
    {
        $sql       = 'UPDATE mall_product SET stock = stock + :stock WHERE productid = :productid';
        $params    = [
            ':stock'     => $stock,
            ':productid' => $productId
        ];
        return Db::execute($sql, $params);
    }
}