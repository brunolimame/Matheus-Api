<?php

namespace Modulo\User\Entity;

use Core\Entity\Entity;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueJson;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;
use Core\Lib\ParametrosArquivo;
use Exception;

class UserEntity extends Entity
{
    const TABLE = 'usuario';

    /** @var EntityValueId */
    public $id;
    /** @var EntityValueUuid */
    public $uuid;
    /** @var EntityValueString */
    public $nome;
    /** @var EntityValueString */
    public $username;
    /** @var EntityValueString */
    public $email;
    /** @var EntityValueString */
    public $password;
    /** @var EntityValueString */
    public $salt;
    /** @var EntityValueString */
    public $nivel;
    /** @var EntityValueJson */
    public $niveis;
    /** @var EntityValueString */
    public $chave;
    /** @var EntityValueString */
    public $foto;
    /** @var EntityValueDatetime */
    public $criado;
    /** @var EntityValueDatetime */
    public $alterado;
    /** @var EntityValueLog */
    public $log;
    /** @var EntityValueBolean */
    public $status;

    public function noToArray()
    {
        return ['niveis'];
    }

    public function carregarDadosExtras(): void
    {
        if (!empty($this->nivel->value())) {
            $this->niveis->set(json_encode(explode(',', $this->nivel->value())));
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function genSalt(): string
    {
        return bin2hex(random_bytes(10));
    }

    /**
     * @param $pass
     * @param $salt
     *
     * @return bool|string
     */
    public function encodePass($pass, $salt)
    {
        if (empty($salt)) {
            return false;
        }

        return $this->genHash($this->genHash($pass) . $this->genHash($salt));
    }

    /**
     * @param        $str
     * @param string $algo
     *
     * @return string
     */
    private function genHash($str, $algo = 'sha512'): string
    {
        return hash($algo, $str);
    }

    /**
     * @param $pass
     * @param $salt
     * @param $senhaAtual
     *
     * @return bool
     */
    public function isValidPass($pass, $salt, $senhaAtual): bool
    {
        if (empty($pass) || empty($salt) || empty($senhaAtual)) {
            return false;
        }

        return hash_equals($this->encodePass($pass, $salt), $senhaAtual);
    }

    /**
     * @param $evento
     * @param $usuario
     * @return array
     * @throws Exception
     */
    public function toSave($evento = null, $usuario = null): array
    {
        if (!$this->id->value()) {
            $this->salt->set($this->genSalt());
            $this->password->set($this->encodePass($this->password->value(), $this->salt->value()));
        }

        return parent::toSave($evento, $usuario);
    }

    static public function getParametrosParaArquivo()
    {
        return ParametrosArquivo::load(
            "/assets/user",
            1,
            [[500, 500, null]],
            ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp']
        );
    }
}