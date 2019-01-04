<?php

namespace finger\Alisdk\Oss\Result;

use finger\Alisdk\Oss\Core\OssException;

/**
 * Class UploadPartResult
 * @package finger\Alisdk\Oss\Result
 */
class UploadPartResult extends Result
{
    /**
     * 结果中part的ETag
     *
     * @return string
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $header = $this->rawResponse->header;
        if (isset($header["etag"])) {
            return $header["etag"];
        }
        throw new OssException("cannot get ETag");

    }
}