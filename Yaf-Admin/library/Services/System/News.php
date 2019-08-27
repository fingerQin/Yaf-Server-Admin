<?php
/**
 * 文章管理。
 * @author fingerQin
 * @date 2016-07-08
 */
namespace Services\System;

use finger\Validator;
use finger\Database\Db;
use Utils\YCore;
use Models\News as NewsModel;
use Models\NewsData;
use Models\Category as CategoryModel;
use Services\System\Category as CategoryService;

class News extends \Services\AbstractBase
{
    /**
     * 获取指定文档的目录。
     *
     * @param  int  $catId  文档分类 ID。
     * @return void
     */
    public static function getDocCatalogue($catId)
    {
        $catList    = CategoryService::getByParentToCategory($catId, CategoryService::CAT_DOC);
        $arrCatCode = array_column($catList, 'cat_code');
        $NewsModel  = new NewsModel();
        $columns    = ['news_id', 'cat_code', 'title', 'intro', 'keywords', 'image_url', 'source', 'modified_time'];
        $where      = [
            'cat_code' => ['IN', $arrCatCode],
            'status'   => NewsModel::STATUS_YES,
            'display'  => NewsModel::STATUS_YES
        ];
        $newsList = $NewsModel->fetchAll($columns, $where, 0, 'listorder ASC');
        $result   = [];
        foreach ($catList as $catInfo) {
            $catCodeNews = [];
            foreach ($newsList as $news) {
                if ($news['cat_code'] == $catInfo['cat_code']) {
                    $catCodeNews[] = $news;
                }
            }
            $result[$catInfo['cat_code']] = [
                'catInfo' => $catInfo,
                'news'    => $catCodeNews
            ];
        }
        return $result;
    }

    /**
     * 文章列表。
     *
     * @param  string  $title      文章标题。
     * @param  string  $adminName  管理员账号(手机)。
     * @param  string  $catCode    分类编码。
     * @param  string  $starttime  开始时间。
     * @param  string  $endtime    截止时间。
     * @param  int     $page       分页页码。
     * @param  int     $count      每页显示记录条数。
     * 
     * @return array
     */
    public static function list($title = '', $adminName = '', $catCode = '', $starttime = '', $endtime = '', $page = 1, $count = 20)
    {
        $offset = self::getPaginationOffset($page, $count);
        $params = [
            ':status' => NewsModel::STATUS_YES
        ];
        $where = ' WHERE a.status = :status';
        if (strlen($title) > 0) {
            $where .= ' AND a.title = :title ';
            $params[':title'] = $title;
        }
        if (strlen($starttime) > 0) {
            $where .= ' AND a.c_time <= :start_time ';
            $params[':start_time'] = $starttime;
        }
        if (strlen($endtime) > 0) {
            $where .= ' AND a.c_time >= :end_time ';
            $params[':end_time'] = $endtime;
        }
        if (strlen($adminName) > 0) {
            $where .= ' AND c.mobile = :mobile ';
            $params[':mobile'] = $adminName;
        }
        if (strlen($catCode) > 0) {
            $where .= ' AND a.cat_code = :cat_code ';
            $params[':cat_code'] = $catCode;
        }
        $sql = "SELECT COUNT(1) AS count FROM finger_news AS a "
             . "INNER JOIN finger_news_data AS b ON(a.news_id=b.news_id) "
             . "INNER JOIN finger_admin_user AS c ON(a.c_by=c.adminid) {$where}";
        $countData = Db::one($sql, $params);
        $total     = $countData ? $countData['count'] : 0;
        $sql = "SELECT a.news_id,a.title,a.image_url,a.display,a.hits,"
             . "a.u_time,a.c_time,c.mobile,c.real_name,d.cat_name "
             . "FROM finger_news AS a "
             . "INNER JOIN finger_admin_user AS c ON(a.c_by=c.adminid) "
             . "INNER JOIN finger_category AS d ON(a.cat_code=d.cat_code) "
             . "{$where} ORDER BY a.listorder ASC, a.news_id DESC LIMIT {$offset},{$count}";
        $list = Db::all($sql, $params);
        $result      = [
            'list'   => $list,
            'total'  => $total,
            'page'   => $page,
            'count'  => $count,
            'isnext' => self::isHasNextPage($total, $page, $count)
        ];
        return $result;
    }

    /**
     * 按文章code获取文章详情。
     *
     * @param  string  $code          文章code。
     * @param  bool    $isGetContent  是否获取文章内容。false：否、true：是。
     * @return array
     */
    public static function getByCodeNewsDetail($code, $isGetContent = false)
    {
        $NewsModel = new NewsModel();
        $data      = $NewsModel->fetchOne([], ['code' => $code, 'status' => NewsModel::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        if ($isGetContent) {
            $NewsDataModel = new NewsData();
            $newsData      = $NewsDataModel->fetchOne([], ['news_id' => $data['news_id']]);
            if ($newsData) {
                return array_merge($data, $newsData);
            } else {
                YCore::exception(STATUS_SERVER_ERROR, '文章数据异常');
            }
        } else {
            return $data;
        }
    }

    /**
     * 按文章ID获取文章详情。
     *
     * @param  int   $newsId        文章ID。
     * @param  bool  $isGetContent  是否获取文章内容。false：否、true：是。
     * @return array
     */
    public static function detail($newsId, $isGetContent = false)
    {
        $NewsModel = new NewsModel();
        $data      = $NewsModel->fetchOne([], ['news_id' => $newsId, 'status' => NewsModel::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        // 如果已经绑定了分类。则读取父分类 ID。
        if (strlen($data['cat_code']) > 0) {
            $CategoryModel     = new CategoryModel();
            $catInfo           = $CategoryModel->fetchOne([], ['cat_code' => $data['cat_code']]);
            $data['parent_id'] = $catInfo ? $catInfo['parentid'] : 0;
        } else {
            $data['parent_id'] = 0;
        }
        if ($isGetContent) {
            $NewsDataModel = new NewsData();
            $newsData      = $NewsDataModel->fetchOne([], ['news_id' => $newsId]);
            if ($newsData) {
                return array_merge($data, $newsData);
            } else {
                YCore::exception(STATUS_SERVER_ERROR, '文章数据异常');
            }
        } else {
            return $data;
        }
    }

    /**
     * 按文章ID获取文章详情(如果不存在返回完整结构的空数据)。
     *
     * @param  int   $newsId        文章ID。
     * @param  bool  $isGetContent  是否获取文章内容。false：否、true：是。
     * @return array
     */
    public static function getNewsDetailOrDefault($newsId, $isGetContent = false)
    {
        $columns   = ['news_id', 'title', 'keywords', 'source', 'hits', 'cat_code'];
        $NewsModel = new NewsModel();
        $data      = $NewsModel->fetchOne($columns, ['news_id' => $newsId, 'status' => NewsModel::STATUS_YES]);
        if (empty($data)) {
            return [
                'news_id'  => 0,
                'title'    => '',
                'keywords' => '',
                'source'   => '',
                'hits'     => '',
                'content'  => ''
            ];
        } else {
            // 如果已经绑定了分类。则读取父分类 ID。
            if (strlen($data['cat_code']) > 0) {
                $CategoryModel     = new CategoryModel();
                $catInfo           = $CategoryModel->fetchOne([], ['cat_code' => $data['cat_code']]);
                $data['parent_id'] = $catInfo ? $catInfo['parentid'] : 0;
            } else {
                $data['parent_id'] = 0;
            }
            if ($isGetContent) {
                $NewsDataModel = new NewsData();
                $newsData      = $NewsDataModel->fetchOne([], ['news_id' => $newsId]);
                if ($newsData) {
                    return array_merge($data, $newsData);
                } else {
                    YCore::exception(STATUS_ERROR, '文章数据异常');
                }
            } else {
                return $data;
            }
        }
    }

    /**
     * 按文章编码获取文章详情。
     *
     * @param  string $code 文章编码。
     * @return array
     */
    public static function getByCodeDetail($code)
    {
        $NewsModel = new NewsModel();
        $data      = $NewsModel->fetchOne([], ['code' => $code, 'status' => NewsModel::STATUS_YES]);
        if (empty($data)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        $NewsDataModel = new NewsData();
        $newsData      = $NewsDataModel->fetchOne([], ['news_id' => $data['news_id']]);
        if ($newsData) {
            return array_merge($data, $newsData);
        } else {
            YCore::exception(STATUS_SERVER_ERROR, '文章数据异常');
        }
    }

    /**
     * 添加文章。
     *
     * @param  int     $adminId   管理员ID。
     * @param  int     $catCode   分类编码。
     * @param  string  $title     文章标题。
     * @param  string  $intro     文章简介。
     * @param  string  $keywords  文章关键词。
     * @param  string  $source    文章来源。
     * @param  string  $imageUrl  文章图片。
     * @param  string  $content   文章内容。
     * @param  int     $display   显示状态：1显示、0隐藏。
     * @return void
     */
    public static function add($adminId, $catCode, $title, $intro, $keywords, $source, $imageUrl, $content, $display = 1)
    {
        $CategoryModel = new CategoryModel();
        $catInfo       = $CategoryModel->fetchOne([], ['cat_code' => $catCode, 'status' => NewsModel::STATUS_YES]);
        if (empty($catInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        $data = [
            'title'     => $title,
            'intro'     => $intro,
            'keywords'  => $keywords,
            'source'    => $source,
            'image_url' => $imageUrl,
            'content'   => $content,
            'display'   => $display
        ];
        $rules = [
            'title'     => '标题|require|len:1:80:1',
            'intro'     => '文章简介|require|len:1:500:1',
            'keywords'  => '文章关键词|require|len:1:100:1',
            'source'    => '文章来源|require|len:1:50:1',
            'image_url' => '文章图片|len:1:150:1|url',
            'content'   => '文章内容|require|len:10:100000:1',
            'display'   => '显示状态|require|integer'
        ];
        Validator::valido($data, $rules);
        $data['c_by']     = $adminId;
        $data['c_time']   = date('Y-m-d H:i:s', time());
        $data['cat_code'] = $catCode;
        $data['status']   = NewsModel::STATUS_YES;
        unset($data['content']);
        $NewsModel = new NewsModel();
        $newsId    = $NewsModel->insert($data);
        if ($newsId > 0) {
            $newsDataModel = new NewsData();
            $data = [
                'content' => $content,
                'news_id' => $newsId
            ];
            $newsDataModel->insert($data);
            return true;
        } else {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 文章编辑。
     *
     * @param  int     $adminId   管理员ID。
     * @param  int     $newsId    文章ID。
     * @param  int     $catCode   分类ID。
     * @param  string  $title     文章标题。
     * @param  string  $intro     文章简介。
     * @param  string  $keywords  文章关键词。
     * @param  string  $source    文章来源。
     * @param  string  $imageUrl  文章图片。
     * @param  string  $content   文章内容。
     * @param  int     $display   显示状态：1显示、0隐藏。
     * @return void
     */
    public static function edit($adminId, $newsId, $catCode, $title, $intro, $keywords, $source, $imageUrl, $content, $display = 1)
    {
        $NewsModel  = new NewsModel();
        $newsDetail = $NewsModel->fetchOne([], ['news_id' => $newsId, 'status' => NewsModel::STATUS_YES]);
        if (empty($newsDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        $CategoryModel = new CategoryModel();
        $catInfo       = $CategoryModel->fetchOne([], ['cat_code' => $catCode, 'status' => NewsModel::STATUS_YES]);
        if (empty($catInfo)) {
            YCore::exception(STATUS_SERVER_ERROR, '分类不存在或已经删除');
        }
        $data = [
            'title'     => $title,
            'intro'     => $intro,
            'keywords'  => $keywords,
            'source'    => $source,
            'image_url' => $imageUrl,
            'content'   => $content,
            'display'   => $display
        ];
        $rules = [
            'title'     => '标题|require|len:1:80:1',
            'intro'     => '文章简介|require|len:1:500:1',
            'keywords'  => '文章关键词|require|len:1:100:1',
            'source'    => '文章来源|require|len:1:50:1',
            'image_url' => '文章图片|len:1:150:1|url',
            'content'   => '文章内容|require|len:10:100000:1',
            'display'   => '显示状态|require|integer'
        ];
        Validator::valido($data, $rules); // 验证不通过会抛异常。
        $data['u_by']     = $adminId;
        $data['u_time']   = date('Y-m-d H:i:s', time());
        $data['cat_code'] = $catCode;
        unset($data['content']);
        $ok = $NewsModel->update($data, ['news_id' => $newsId, 'status' => NewsModel::STATUS_YES]);
        if ($ok) {
            $NewsDataModel = new NewsData();
            $data  = ['content' => $content];
            $where = ['news_id' => $newsId];
            $NewsDataModel->update($data, $where);
            return true;
        } else {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 删除文章。
     *
     * @param  int  $adminId  管理员ID。
     * @param  int  $newsId   文章ID。
     * @return bool
     */
    public static function delete($adminId, $newsId)
    {
        $NewsModel  = new NewsModel();
        $NewsDetail = $NewsModel->fetchOne([], ['news_id' => $newsId, 'status' => NewsModel::STATUS_YES]);
        if (empty($NewsDetail)) {
            YCore::exception(STATUS_SERVER_ERROR, '文章不存在或已经删除');
        }
        $where = [
            'news_id' => $newsId
        ];
        $data = [
            'status' => NewsModel::STATUS_DELETED,
            'u_by'   => $adminId,
            'u_time' => date('Y-m-d H:i:s', time())
        ];
        $ok = $NewsModel->update($data, $where);
        if (!$ok) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
    }

    /**
     * 文章排序。
     *
     * @param  int    $adminId     管理员ID。
     * @param  array  $listorders  分类排序数据。[ ['文章ID' => '排序值'], ...... ]
     * @return bool
     */
    public static function sort($adminId, $listorders)
    {
        if (empty($listorders)) {
            YCore::exception(STATUS_SERVER_ERROR, '请选择要排序的文章');
        }
        $NewsModel = new NewsModel();
        foreach ($listorders as $newsId => $sortValue) {
            $data = [
                'listorder' => $sortValue,
                'u_by'      => $adminId,
                'u_time'    => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'news_id' => $newsId,
                'status'  => NewsModel::STATUS_YES
            ];
            $NewsModel->update($data, $where);
        }
    }
}