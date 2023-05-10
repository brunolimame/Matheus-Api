<?php

namespace Boot\Provider\Email\PHPmailer;

use Boot\Provider\Email\EnvioEmailInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class PHPmailerMessage extends PHPMailer implements EnvioEmailInterface
{

    public function __construct($exceptions = null)
    {
        parent::__construct($exceptions);
    }

    public function setAssunto($assunto)
    {
        $this->Subject = $assunto;
        return $this;
    }

    public function setCorpo($corpo, $contentType = 'text/html', $charset = 'utf8')
    {
        $this->Body = $corpo;
        $this->isHTML($contentType == 'text/html');
        $this->CharSet = $charset;
        return $this;
    }

    public function setCorpoHtmlText($corpoHtml, $corpoTexto)
    {
        $this->Body    = $corpoTexto;
        $this->AltBody = $corpoTexto;
        return $this;
    }

    public function setDe($email, $nome = null)
    {
        $this->setFrom($email, $nome);
        return $this;
    }

    public function setPara($email, $nome = null)
    {
        $this->addAddress($email, $nome);
        return $this;
    }

    public function setResponderPara($email, $nome = null)
    {
        $this->addReplyTo($email, $nome);
        return $this;
    }

    public function setEnviarErroPara($email)
    {
        throw new \Exception('Função não implementada.');
    }

    public function setCopia($email, $nome = null)
    {
        $this->addCc($email, $nome);
        return $this;
    }

    public function setCopiaOculta($email, $nome = null)
    {
        $this->addBcc($email, $nome);
        return $this;
    }

    public function setContentType($contentType = 'text/html'){
        throw new \Exception('Função não implementada.');
    }
    

    function addArquivo($caminhoArquivo = null, $nome = '')
    {
        if (!empty($caminhoArquivo)) {
            $this->addAttachment($caminhoArquivo, $nome);
        }
        return $this;
    }

    static public function loadMailer($args): self
    {
        $mailer = new self(true);
        $mailer->SMTPDebug = $args->SMTPDebug;
        $mailer->isSMTP();
        $mailer->Host     = $args->host;
        $mailer->SMTPAuth = $args->SMTPAuth;
        $mailer->Username = $args->username;
        $mailer->Password = $args->password;

        return $mailer;
    }

    public function enviar()
    {
        try {
            return $this->send();
        } catch (PHPMailerException $e) {
            throw new \Exception(sprintf("Erro ao enviar mensagem: [ERROR-MAILER] %s", $e->errorMessage()));
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Erro ao enviar mensagem: [ERROR] %s", $e->errorMessage()));
        }
    }
}