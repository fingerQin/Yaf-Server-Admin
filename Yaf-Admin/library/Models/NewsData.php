<?php
/**
 * 文章数据表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

class NewsData extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_news_data';

    protected $primaryKey = 'newsid';
}