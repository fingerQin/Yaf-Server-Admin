<?php
/**
 * 微信支付API异常类
 * @author fingerQin
 */
namespace finger\WeChat\Pay;

class WxPayException extends \Exception {

    public function errorMessage() {
        return $this->getMessage();
    }

}
