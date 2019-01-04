<?php

namespace finger\Alisdk\Oss\Result;


/**
 * Class BodyResult
 * @package finger\Alisdk\Oss\Result
 */
class BodyResult extends Result
{
    /**
     * @return string
     */
    protected function parseDataFromResponse()
    {
        return empty($this->rawResponse->body) ? "" : $this->rawResponse->body;
    }
}