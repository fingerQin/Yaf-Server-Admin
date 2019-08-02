<?php
/**
 * 聚合文章主表。
 * @author fingerQin
 * @date 2019-07-26
 */

namespace Models;

class RssNews extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_rss_news';

    protected $primaryKey = 'newsid';

    /**
     * 是否外链。
     */
    const OUTSITE_URL_YES = 1; // 是。
    const OUTSITE_URL_NO  = 0; // 否。
}