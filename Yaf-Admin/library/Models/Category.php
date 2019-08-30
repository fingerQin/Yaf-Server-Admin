<?php
/**
 * 分类表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

class Category extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_category';

    protected $primaryKey = 'cat_id';

    /**
     * 设置分类排序值。
     *
     * @param  int    $catId    分类ID。
     * @param  array  $sortVal  排序值。
     * @return bool
     */
    public function sort($catId, $sortVal)
    {
        $data = [
            'listorder' => $sortVal,
            'u_time'    => date('Y-m-d H:i:s', time())
        ];
        $where = [
            'cat_id' => $catId
        ];
        return $this->update($data, $where);
    }
}