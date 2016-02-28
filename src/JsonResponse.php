<?php namespace KamranAhmed\Smasher;

use KamranAhmed\Smasher\Contracts\ResponseContract;

/**
 * JsonResponse
 *
 * Helps encoding and decoding arrays to JSON
 */
class JsonResponse implements ResponseContract
{
    /**
     * Formats the passed data/array to json
     * @param  array $data The data which is to be encoded
     * @return string
     */
    public function encode($data)
    {
        return json_encode($data);
    }

    /**
     * Decodes the passed string and creates array from it
     * @param  string $response The existing response which is to be decoded to array
     * @return array
     */
    public function decode($response)
    {
        return json_decode($response, true);
    }
}
