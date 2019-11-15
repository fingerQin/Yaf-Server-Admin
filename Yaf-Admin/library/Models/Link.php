<?php
/**
 * 友情链接表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

class Link extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_link';

    protected $primaryKey = 'link_id';

    /**
     * 设置广告排序值。
     *
     * @param  int   $linkId  友情链接ID。
     * @param  array $sortVal 排序值。
     * @return bool
     */
    public function sortLink($linkId, $sortVal)
    {
        $data = [
            'listorder' => $sortVal
        ];
        $where = [
            'link_id' => $linkId
        ];
        return $this->update($data, $where);
    }
}