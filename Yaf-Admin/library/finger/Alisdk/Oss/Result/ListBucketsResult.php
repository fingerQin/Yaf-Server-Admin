<?php

namespace finger\Alisdk\Oss\Result;

use finger\Alisdk\Oss\Model\BucketInfo;
use finger\Alisdk\Oss\Model\BucketListInfo;

/**
 * Class ListBucketsResult
 *
 * @package finger\Alisdk\Oss\Result
 */
class ListBucketsResult extends Result
{
    /**
     * @return BucketListInfo
     */
    protected function parseDataFromResponse()
    {
        $bucketList = array();
        $content = $this->rawResponse->body;
        $xml = new \SimpleXMLElement($content);
        if (isset($xml->Buckets) && isset($xml->Buckets->Bucket)) {
            foreach ($xml->Buckets->Bucket as $bucket) {
                $bucketInfo = new BucketInfo(strval($bucket->Location),
                    strval($bucket->Name),
                    strval($bucket->CreationDate));
                $bucketList[] = $bucketInfo;
            }
        }
        return new BucketListInfo($bucketList);
    }
}