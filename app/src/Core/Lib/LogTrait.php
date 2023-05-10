<?php

namespace Core\Lib;

use Core\Lib as CoreLib;

trait LogTrait
{
    use CoreLib\IPTrait;

    /**
     * @param array $user
     * @param $event
     * @param null $oldLog
     * @param bool $inArray
     * @return array|string
     * @throws \Exception
     */
    public function getArrayLog($user = [], $event, &$oldLog = null, $inArray = false)
    {
        $novoLog[] = [
            'user'  => $user,
            'event' => $event,
            'ip'    => $this->getIpCliente(),
            'data'  => (new \DateTime('now'))->format('Y-m-d H:i:s')
        ];

        if (!empty($oldLog)) {
            $oldLogArray = unserialize($oldLog);
            if (is_array($oldLogArray)) {
                foreach ($oldLogArray as $l) {
                    $novoLog[] = $l;
                }
            }
        }

        if ($inArray) {
            return $novoLog;
        } else {
            return serialize($novoLog);
        }

    }
}