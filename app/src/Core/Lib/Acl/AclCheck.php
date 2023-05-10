<?php

namespace Core\Lib\Acl;

use Laminas\Permissions\Acl\Acl;
use Boot\Provider\Jwt\JwtParse;

class AclCheck
{
    /** @var Acl */
    public $acl;
    public $recurso;
    /** @var JwtParse */
    public $jwtParse;

    public function __construct(Acl &$acl, JwtParse $jwtParse, $recurso = null)
    {
        $this->acl = $acl;
        $this->jwtParse = $jwtParse;
        $this->recurso = $recurso;
    }

    public function isAllowed($funcao=null)
    {
        return $this->acl->isAllowed($this->jwtParse->nivel, $this->recurso, $funcao);
    }

    public function isAuth()
    {
        return $this->isAllowed('auth') && $this->jwtParse->isPrivate() && !empty($this->jwtParse->tokenDecode['uuid']);
    }

    public function isAdmin()
    {
        return $this->isAuth() && $this->isAllowed('admin');
    }
}
