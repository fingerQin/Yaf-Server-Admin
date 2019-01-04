<?php

namespace finger\Alisdk\Oss\Result;

use finger\Alisdk\Oss\Core\OssException;

/**
 * Class AppendResult
 * @package finger\Alisdk\Oss\Result
 */
class AppendResult extends Result
{
    /**
     * 结果中part的next-append-position
     *
     * @return int
     * @throws OssException
     */
    protected function parseDataFromResponse()
    {
        $header = $this->rawResponse->header;
        if (isset($header["x-oss-next-append-position"])) {
            return intval($header["x-oss-next-append-position"]);
        }
        throw new OssException("cannot get next-append-position");
    }
}