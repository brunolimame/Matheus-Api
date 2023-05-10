<?php

namespace Core\Lib;


trait IPTrait
{

    /**
     * @return string
     */
    public function getIpCliente()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $ipx = explode(',', $ip);
        if (empty($ipx)) {
            return $ip;
        }

        $listIp = [];

        foreach ($ipx as $i) {
            $listIp[] = trim($i);
        }

        $result = array_unique($listIp);
        return (string) current($result);

    }
}