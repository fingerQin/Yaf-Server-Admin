<?php
/**
 * 文章表 Model。
 * 
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Models;

use Utils\YDate;
use finger\Database\Db;

class News extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_news';

    protected $primaryKey = 'news_id';
}