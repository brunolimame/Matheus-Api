<?php

namespace Modulo\Usuario\Provider;

use Core\Lib\Acl\AclCheck;
use Boot\Provider\Jwt\JwtParse;
use Laminas\Permissions\Acl\Acl;

class UsuarioAclProvider
{
    /**@var Acl */
    public $acl;
    /**@var JwtParse */
    public $jwtParse;

    public function __construct(Acl $acl, JwtParse $jwtParse)
    {
        $this->acl = $acl;
        $this->jwtParse = $jwtParse;
    }

    public function getAcl()
    {
        return new AclCheck($this->acl, $this->jwtParse, 'usuario');
    }
}
