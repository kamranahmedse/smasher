<?php namespace KamranAhmed\Smasher;

use KamranAhmed\Smasher\Contracts\ResponseContract;

class JsonResponse implements ResponseContract
{
    public function encode($data)
    {
        return json_encode($data);
    }

    public function decode($response)
    {
        return json_decode($response, true);
    }
}
