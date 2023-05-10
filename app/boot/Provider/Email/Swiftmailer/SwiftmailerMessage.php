<?php

namespace Boot\Provider\Email\Swiftmailer;

use Boot\Provider\Email\EnvioEmailInterface;
use Swift_Message;

class SwiftmailerMessage extends Swift_Message implements EnvioEmailInterface
{
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_TEXT = 'plain/text';

    static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new static($subject, $body, $contentType, $charset);
    }

    public function setAssunto($assunto)
    {
        $this->setSubject($assunto);
        return $this;
    }

    public function setCorpo($corpo, $contentType = 'text/html', $charset = 'utf8')
    {
        $this->setBody($corpo, $contentType, $charset);
        return $this;
    }

    public function setCorpoHtmlText($corpoHtml, $corpoTexto)
    {
        $this->setBody($corpoTexto, self::CONTENT_TYPE_HTML)
            ->addPart($corpoTexto, self::CONTENT_TYPE_TEXT);
        return $this;
    }

    public function setDe($email, $nome = null)
    {
        $this->setFrom($email, $nome);
        return $this;
    }

    public function setPara($email, $nome = null)
    {
        $this->setTo($email, $nome);
        return $this;
    }

    public function setResponderPara($email, $nome = null)
    {
        $this->setReplyTo($email, $nome);
        return $this;
    }

    public function setEnviarErroPara($email)
    {
        $this->setReturnPath($email);
        return $this;
    }

    public function setCopia($email, $nome = null)
    {
        $this->setCc($email, $nome);
        return $this;
    }

    public function setCopiaOculta($email, $nome = null)
    {
        $this->setBcc($email, $nome);
        return $this;
    }

    public function setContentType($contentType = 'text/html')
    {
        parent::setContentType($contentType);
        return $this;
    }


    function addArquivo($caminhoArquivo = null)
    {
        if (!empty($caminhoArquivo)) {
            $this->attach(\Swift_Attachment::fromPath($caminhoArquivo));
        }
        return $this;
    }
}