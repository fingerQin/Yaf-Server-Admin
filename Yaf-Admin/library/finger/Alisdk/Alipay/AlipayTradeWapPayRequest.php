<?php
/**
 * ALIPAY API: alipay.trade.wap.pay request
 *
 * @author auto create
 * @since 1.0, 2016-08-12 11:20:54
 */

class AlipayTradeWapPayRequest
{
    /** 
     * 手机网站支付接口2.0
     **/
    private $bizContent;
    
    private $terminalType;
    private $terminalInfo;
    private $prodCode;
    private $notifyUrl;
    private $returnUrl;
    private $apiVersion  = "1.0";
    private $apiParas    = [];
    private $needEncrypt = false;

    
    public function setBizContent($bizContent)
    {
        $this->bizContent = $bizContent;
        $this->apiParas["biz_content"] = $bizContent;
    }

    public function getBizContent()
    {
        return $this->bizContent;
    }

    public function getApiMethodName()
    {
        return "alipay.trade.wap.pay";
    }

    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl=$notifyUrl;
    }

    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl=$returnUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function getTerminalType()
    {
        return $this->terminalType;
    }

    public function setTerminalType($terminalType)
    {
        $this->terminalType = $terminalType;
    }

    public function getTerminalInfo()
    {
        return $this->terminalInfo;
    }

    public function setTerminalInfo($terminalInfo)
    {
        $this->terminalInfo = $terminalInfo;
    }

    public function getProdCode()
    {
        return $this->prodCode;
    }

    public function setProdCode($prodCode)
    {
        $this->prodCode = $prodCode;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion=$apiVersion;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setNeedEncrypt($needEncrypt)
    {
        $this->needEncrypt=$needEncrypt;
    }

    public function getNeedEncrypt()
    {
        return $this->needEncrypt;
    }

}
