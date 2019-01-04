<?php

namespace finger\Alisdk\Oss\Result;


/**
 * Class PutSetDeleteResult
 * @package finger\Alisdk\Oss\Result
 */
class PutSetDeleteResult extends Result
{
    /**
     * @return array()
     */
    protected function parseDataFromResponse()
    {
        $body = array('body' => $this->rawResponse->body);
        return array_merge($this->rawResponse->header, $body);
    }
}
