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
     * @param  string  $keyword     搜索关键词。模糊搜索商品名称。
     * @param  int     $catId       分类ID。-1全部。
     * @param  int     $startPrice  价格最小值。
     * @param  int     $endPrice    价格最大值。
     * @param  int     $page        当前页码。
     * @param  int     $count       每页显示条数。
     * @return array
     */
    public static function list($keyword = '', $catId = -1, $startPrice = -1, $endPrice = -1, $page = 1, $count = 20)
    {
        $offset    = self::getPaginationOffset($page, $count);
        $fromTable = ' FROM mall_goods ';
        $columns   = ' goodsid,goods_name,min_price,max_price,goods_img,buy_count,month_buy_count ';
        $where     = ' WHERE status = :status AND marketable = :marketable ';
        $params    = [
            ':status'     => MallGoods::STATUS_YES,
            ':marketable' => MallGoods::STATUS_YES
        ];
        if (strlen($keyword) > 0) {
            $where .= ' AND goods_name LIKE :goods_name ';
            $params[':goods_name'] = "%{$keyword}%";
        }
        if ($catId != -1) {
            $CategoryModel = new Category();
            $catInfo = $CategoryModel->fetchOne([], [
                'cat_id' => $catId,
                'status' => Category::STATUS_YES
            ]);
            if (empty($catInfo)) {
                $where .= ' AND cat_code = :cat_code ';
                $params[':cat_code'] = '';
            } else {
                $where .= ' AND cat_code LIKE :cat_code ';
                $catCodePrefix = CategoryService::getCatCodePrefix($catInfo['cat_code'], $catInfo['lv']);
                $params[':cat_code'] = "{$catCodePrefix}%";
            }
        }
        if ($startPrice != -1) {
            if (!Validator::is_integer($startPrice)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询价格必须是整数');
            }
            $where .= ' AND min_price <= :start_price ';
            $params[':start_price'] = $startPrice;
        }
        if ($endPrice != -1) {
            if (!Validator::is_integer($endPrice)) {
                YCore::exception(STATUS_SERVER_ERROR, '查询价格必须是整数');
            }
            $where .= ' AND max_price >= :end_price ';
            $params[':end_price'] = strtotime($endPrice);
        }
        $orderBy   = ' ORDER BY listorder ASC, goodsid DESC ';
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
            'isnext' => self::IsHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取商品详情。
     *
     * @param  int $goodsId  商品ID。
     * @return array
     */
    public static function detail($goodsId)
    {
        $GoodsModel = new MallGoods();
        $columns    = [
            'goodsid',
            'goods_name',
            'min_market_price',
            'max_market_price',
            'min_price',
            'max_price',
            'goods_img',
            'buy_count',
            'month_buy_count',
            'limit_count',
            'spec_val_json',
            'description'
        ];
        $goodsDetail = $GoodsModel->fetchOne($columns, ['goodsid' => $goodsId, 'status' => MallGoods::STATUS_YES]);
        if (empty($goodsDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '商品不存在');
        }
        // 商品相册。
        $GoodsImageModel = new MallGoodsImage();
        $goodsImages     = $GoodsImageModel->fetchAll(['image_url'], [
            'goodsid' => $goodsId, 
            'status'  => MallGoodsImage::STATUS_YES
        ], 5, 'imageid ASC');
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
     * 获取商品库存总数。
     *
     * @param  int  $goodsId  商品ID。
     * @return int
     */
    protected static function getGoodsStock($goodsId)
    {
        $sql = 'SELECT SUM(stock) AS stock FROM mall_product WHERE goodsid = :goodsid AND status = :status';
        $params = [
            ':goodsid' => $goodsId,
            ':status'  => MallProduct::STATUS_NORMAL
        ];
        $data = Db::one($sql, $params);
        return $data ? $data['stock'] : 0;
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
        $stock  = intval($stock); // 由于 stock = stock - :stock 使用PDO不支持。所以，直接写在SQL里面要进行强制类型转换避免注入。
        $sql    = "UPDATE mall_product SET stock = stock - {$stock} WHERE productid = :productid AND stock >= :stock";
        $params = [
            ':stock'     => $stock,
            ':productid' => $productId
        ];
        $status = Db::execute($sql, $params);
        return $status ? true : false;
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
        $sql    = 'UPDATE mall_product SET stock = stock + :stock WHERE productid = :productid';
        $params = [
            ':stock'     => $stock,
            ':productid' => $productId
        ];
        return Db::execute($sql, $params);
    }
}