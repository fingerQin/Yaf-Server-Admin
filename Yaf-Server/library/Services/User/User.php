<?php
/**
 * 用户相关业务接口。
 * 
 * @author fingerQin
 * @date 2018-08-15
 */

namespace Services\User;

use Services\Gold\Gold;

class User extends \Services\AbstractBase
{
    /**
     * 获取用户详情。
     * 
     * -- 后续会整合其他信息合并进来。
     *
     * @param  int  $userid  用户 ID。
     * @return array
     */
    public static function detail($userid)
    {
        $columns   = 'mobile, open_id, nickname, headimg, intro, c_time AS reg_time';
        $UserModel = new \Models\User();
        $detail    = $UserModel->fetchOne($columns, ['userid' => $userid]);
        $detail['gold'] = Gold::userGoldCount($userid);
        return $detail;
    }
}