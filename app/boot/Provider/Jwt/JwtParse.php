<?php

namespace Boot\Provider\Jwt;


class JwtParse
{
    public $tokenDecode;
    public $nivel;

    public function __construct($tokenDecode=null)
    {
        $this->tokenDecode = $tokenDecode;
        $this->nivel = $this->isPublic() ? "convidado" : $this->tokenDecode['nivel'];
    }

    public function getTypeToken()
    {
        return $this->tokenDecode['type'];
    }
    
    public function isPublic()
    {
        return $this->getTypeToken() == JwtController::TOKEN_PUBLIC;
    }

    public function isPrivate()
    {
        return $this->getTypeToken() == JwtController::TOKEN_PRIVATE;
    }

    public function isRefresh()
    {
        return $this->getTypeToken() == JwtController::TOKEN_REFRESH;
    }
}
