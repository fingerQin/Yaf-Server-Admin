<?php

namespace finger\Alisdk\Oss\Result;

use finger\Alisdk\Oss\Core\OssException;


/**
 * Class initiateMultipartUploadResult
 * @package finger\Alisdk\Oss\Result
 */
class InitiateMultipartUploadResult extends Result
{
    /**
     * 结果中获取uploadId并返回
     *
     * @throws OssException
     * @return string
     */
    protected function parseDataFromResponse()
    {
        $content = $this->rawResponse->body;
        $xml = simplexml_load_string($content);
        if (isset($xml->UploadId)) {
            return strval($xml->UploadId);
        }
        throw new OssException("cannot get UploadId");
    }
}