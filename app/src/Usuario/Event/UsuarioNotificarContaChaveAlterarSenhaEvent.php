<?php

namespace Modulo\Usuario\Event;

use Modulo\Usuario\Entity\UsuarioEntity;
use Modulo\Usuario\Provider\UsuarioViewProvider;
use Boot\Provider\Email\PHPmailer\PHPmailerMessage;

class UsuarioNotificarContaChaveAlterarSenhaEvent
{

    public $usuarioEntity;
    public $PHPmailerMessage;
    public $view;
 
    public function __construct(PHPmailerMessage $PHPmailerMessage, UsuarioViewProvider $view)
    {
        $this->PHPmailerMessage = $PHPmailerMessage;
        $this->view = $view->getView();
    }

    public function enviar()
    {
        $emailBody = $this->view->renderHtml('conta-recuperada', ['usuario' => $this->usuarioEntity]);
        $this->PHPmailerMessage
            ->setAssunto("Recuperação de senha finalizada")
            ->setPara($this->usuarioEntity->email->value())
            ->setCorpo($emailBody)
            ->enviar();
    }

    public function setUsuario(UsuarioEntity $usuarioEntity)
    {
        $this->usuarioEntity = $usuarioEntity;
        return $this;
    }
}
