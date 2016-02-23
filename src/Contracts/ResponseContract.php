<?php namespace KamranAhmed\SquashDir\Contracts;

/**
 * Interface Response Contract
 */
interface ResponseContract {

    /**
     * Formats the passed data
     * @param  array $data The data which is to be formatted
     * @return mixed
     */
    public function format($data);

    /**
     * Creates array from the passed response
     * @param  string $response The existing response to be formatted to array
     * @return array
     */
    public function toArray($response);
}