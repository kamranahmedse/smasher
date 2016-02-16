<?php

class JsonResponse implements ResponseContract
{
    public function format($data) {
        return json_encode($data);
    }

    public function toArray( $response ) {
        return json_decode($response, true);
    }
}