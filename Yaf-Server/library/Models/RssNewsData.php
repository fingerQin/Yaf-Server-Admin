<?php
/**
 * 聚合文章副表。
 * @author fingerQin
 * @date 2019-07-26
 */

namespace Models;

class RssNewsData extends AbstractBase
{
    /**
     * 表名。
     *
     * @var string
     */
    protected $tableName  = 'finger_rss_news_data';

    protected $primaryKey = 'newsid';
}