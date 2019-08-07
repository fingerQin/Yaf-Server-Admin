<?php
/**
 * 货品表。
 * @author fingerQin
 * @date 2019-08-07
 */

namespace Models;

class MallProduct extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName = 'mall_product';

    /**
     * 删除指定商品的货品。
     *
     * @param  int  $userid   操作用户ID。
     * @param  int  $goodsId  商品ID。
     * @return bool
     */
    public function deleteGoodsProduct($userid, $goodsId)
    {
        $where = [
            'goodsid' => $goodsId,
            'status'  => self::STATUS_YES
        ];
        $data = [
            'status' => self::STATUS_DELETED,
            'c_by'   => $userid,
            'c_time' => date('Y-m-d H:i:s', time())
        ];
        return $this->update($data, $where);
    }
}