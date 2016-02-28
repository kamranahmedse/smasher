<?php namespace KamranAhmed\Smasher\Contracts;

/**
 * Interface Response Contract
 */
interface ResponseContract
{
    /**
     * Formats the passed data for example a `JsonResponse` will encode to json, `XMLResponse`
     * will encode to xml etc
     * @param  array $data The data which is to be formatted
     * @return string
     */
    public function encode($data);

    /**
     * Decodes the passed string and creates array from it
     * @param  string $response The existing response which is to be decoded to array
     * @return array
     */
    public function decode($response);
}
