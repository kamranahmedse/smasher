<?php

/**
 * Interface Response Contract
 */
interface FormatterContract {

    /**
     * Formats the passed data
     * @param  array $data The data which is to be formatted
     * @return mixed
     */
    public function format($data);
}