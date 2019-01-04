<?php

namespace finger\Alisdk\Oss\Result;


/**
 * Class DeleteObjectsResult
 * @package finger\Alisdk\Oss\Result
 */
class DeleteObjectsResult extends Result
{
    /**
     * @return array()
     */
    protected function parseDataFromResponse()
    {
        $body = $this->rawResponse->body;
        $xml = simplexml_load_string($body); 
        $objects = array();

        if (isset($xml->Deleted)) {
            foreach($xml->Deleted as $deleteKey)
                $objects[] = $deleteKey->Key;
        }
        return $objects;
    }
}
