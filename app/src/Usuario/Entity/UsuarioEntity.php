<?php

namespace Modulo\Usuario\Entity;

use Core\Entity\Entity;
use Core\Lib\ParametrosArquivo;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Core\Entity\Value\EntityValueId;
use Core\Entity\Value\EntityValueLog;
use Core\Entity\Value\EntityValueUuid;
use Core\Entity\Value\EntityValueBolean;
use Core\Entity\Value\EntityValueString;
use Core\Entity\Value\EntityValueDatetime;

class UsuarioEntity extends Entity
{
    const NIVEIS = [
        'usuario' => 'Usuário',
        'moderador' => 'Moderador',
        'admin' => 'Administrador',
        'sadmin' => 'Super Administrador',
    ];
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


    public function carregarDadosExtras(): void
    {
//        $this->nivelNome->set(!empty($this->nivel->value()) ? self::NIVEIS[$this->nivel->value()] : null);
    }

    public function noToArray()
    {
        return ['nivelNome'];
    }


    public function genChave()
    {
        $this->chave->set(RamseyUuid::uuid4()->toString());
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function genSalt()
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
    private function genHash($str, $algo = 'sha512')
    {
        return hash($algo, $str);
    }


    static public function getListaNiveis()
    {
        return array_filter(self::NIVEIS, function ($value, $key) {
            return $key != 'sadmin';
        }, ARRAY_FILTER_USE_BOTH);
    }
    /**
     * @param $pass
     * @param $salt
     * @param $senhaAtual
     *
     * @return bool
     */
    public function isValidPass($pass, $salt, $senhaAtual)
    {
        if (empty($pass) || empty($salt) || empty($senhaAtual)) {
            return false;
        }

        return hash_equals($this->encodePass($pass, $salt), $senhaAtual);
    }


    public function toSave($evento = null, $usuario = null)
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
            "/assets/usuario",
            3,
            [
                [600, 600, null],
                [300, 300, 'media'],
                [300, 300, 'thumb']
            ],
            ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-windows-bmp']
        );
    }
}
