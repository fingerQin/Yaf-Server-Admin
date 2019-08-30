<?php
/**
 * 文件表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

use finger\Database\Db;

class Files extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_files';

    protected $primaryKey = 'file_id';

    /**
     * 获取文件列表。
     *
     * @param  int     $userType   用户类型：－1全部、1管理员、2普通用户 。
     * @param  int     $userid     用户ID或管理员ID。
     * @param  string  $fileMd5    文件md5值。
     * @param  int     $fileType   文件类型：1-图片、2-其他文件。
     * @param  string  $startTime  文件上传时间开始。
     * @param  string  $endTime    文件上传时间截止。
     * @param  int     $page       当前页码。
     * @param  int     $count      每页显示条数。
     * @return array
     */
    public function getList($userType, $userid, $fileMd5, $fileType, $startTime, $endTime, $page, $count)
    {
        $offset  = $this->getPaginationOffset($page, $count);
        $columns = ' * ';
        $where   = ' WHERE status = :status ';
        $params  = [
            ':status' => Files::STATUS_YES
        ];
        if (strlen($fileMd5) > 0) {
            $where .= ' AND file_md5 = :file_md5 ';
            $params[':file_md5'] = $fileMd5;
        }
        if (strlen($startTime) > 0) {
            $where .= ' AND c_time > :start_time ';
            $params[':start_time'] = $startTime;
        }
        if (strlen($endTime) > 0) {
            $where .= ' AND c_time < :end_time ';
            $params[':end_time'] = $endTime;
        }
        if ($fileType != -1) {
            $where .= ' AND file_type = :file_type ';
            $params[':file_type'] = $fileType;
        }
        switch ($userType) {
            case 1:
            case 2:
                $where .= ' AND user_type = :user_type AND user_id = :user_id ';
                $params[':user_type'] = $userType;
                $params[':user_id']   = $userid;
                break;
            case -1:
                break;
            default: // 查询不到。
                $where .= ' AND user_type = :user_type AND user_id = :user_id ';
                $params[':user_type'] = -1;
                $params[':user_id']   = -1;
                break;
        }
        $orderBy   = ' ORDER BY file_id DESC ';
        $sql       = "SELECT COUNT(1) AS count FROM {$this->tableName} {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql       = "SELECT {$columns} FROM {$this->tableName} {$where} {$orderBy} LIMIT {$offset},{$count}";
        $list      = Db::all($sql, $params);
        $result    = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => $this->isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 获取一组文件。
     * -- 1、如果取一个不存在的文件。此file_id对应的数据会没有。
     *
     * @param  array  $arrFileId
     * @return array
     */
    public function getFile($arrFileId)
    {
        if (empty($arrFileId)) {
            return [];
        }
        $where = [
            'file_id' => ['IN', $arrFileId],
            'status'  => Files::STATUS_YES
        ];
        $whereInfo = $this->parseWhereCondition($where);
        $sql       = "SELECT file_id,file_name FROM {$this->tableName} WHERE {$whereInfo['where']}";
        $params    = $whereInfo['params'];
        return Db::all($sql, $params);
    }

    /**
     * 删除文件。
     *
     * @param  int  $fileId  文件ID。
     * @return bool
     */
    public function deleteFile($fileId)
    {
        $data = [
            'status' => Files::STATUS_DELETED
        ];
        $where = [
            'file_id' => $fileId,
            'status'  => Files::STATUS_YES
        ];
        return $this->update($data, $where);
    }

    /**
     * 添加文件。
     *
     * @param  string  $fileName  文件名。
     * @param  int     $fileType  文件类型。
     * @param  int     $fileSize  文件大小。
     * @param  string  $fileMd5   文件MD5值。
     * @param  int     $userType  用户类型。
     * @param  int     $userid    用户ID。
     * @return int 文件ID。
     */
    public function addFiles($fileName, $fileType, $fileSize, $fileMd5, $userType = 2, $userid = 0)
    {
        $data = [
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_md5'  => $fileMd5,
            'user_type' => $userType,
            'user_id'   => $userid,
            'status'    => Files::STATUS_YES,
            'c_time'    => date('Y-m-d H:i:s', time())
        ];
        return $this->insert($data);
    }
}