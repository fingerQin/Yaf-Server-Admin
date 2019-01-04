<?php
/**
 * 管理后台菜单表 Model。
 * @author fingerQin
 * @date 2018-07-06
 */

namespace Models;

class AdminMenu extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName = 'finger_admin_menu';

    protected $primaryKey = 'menuid';

    /**
     * 设置菜单排序值。
     *
     * @param  int   $menuId   菜单ID。
     * @param  array $sortVal  排序值。
     * @return bool
     */
    public function sort($menuId, $sortVal)
    {
        $data = [
            'listorder' => $sortVal,
            'u_time'    => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ];
        $where = [
            'menuid' => $menuId
        ];
        return $this->update($data, $where);
    }
}