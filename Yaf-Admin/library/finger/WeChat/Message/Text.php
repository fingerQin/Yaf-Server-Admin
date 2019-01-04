<?php
/**
 * 文本消息封装。
 * @author fingerQin
 * @date 2016-05-12
 */
namespace finger\WeChat\Message;

class Text extends AbstractMessage {

    /**
     * 消息类型。
     *
     * @var string
     */
    public $MsgType = 'text';

    /**
     * 文本消息类型。
     *
     * @var string
     */
    protected $Content = '';

    /**
     * 非公用部分。
     *
     * @var string
     */
    protected $propertys = [
        'Content'
    ];

    /**
     * 构造方法。
     *
     * @param string $content 文本消息内容。
     */
    public function __construct($content) {
        $this->Content = $content;
    }

}