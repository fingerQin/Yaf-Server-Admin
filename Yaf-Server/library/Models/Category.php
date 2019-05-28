<?php
/**
 * 系统分类表 Model。
 * @author fingerQin
 * @date 2018-09-05
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
     * 是否外部 URL。
     */
    const IS_EXTERNAL_NO  = 0;
    const IS_EXTERNAL_YES = 1;

    /**
     * 文章分类。
     */
    const CAT_NEWS  = 1;  // 文章分类。
    const CAT_LINK  = 2;  // 友情链接分类。
    const CAT_GOODS = 3;  // 商品分类。

    /**
     * 分类类型。
     *
     * @var array
     */
    public static $categoryTypeList = [
        self::CAT_NEWS  => '文章分类',
        self::CAT_LINK  => '友情链接',
        self::CAT_GOODS => '商品分类'
    ];
}