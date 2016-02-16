<?php

class JsonFormatter implements FormatterContract
{
    public function __construct() {

    }

    public function format($data) {
        return json_encode($data);
    }
}