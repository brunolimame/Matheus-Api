<?php

namespace Boot\Provider\Email;

interface EnvioEmailInterface
{
    public function setAssunto($assunto);

    public function setCorpo($corpo, $contentType = 'text/html', $charset = 'utf8');

    public function setCorpoHtmlText($corpoHtml, $corpoTexto);

    public function setDe($email, $nome = null);

    public function setPara($email, $nome = null);

    public function setResponderPara($email, $nome = null);

    public function setEnviarErroPara($email);

    public function setCopia($email, $nome = null);

    public function setCopiaOculta($email, $nome = null);

    public function setContentType($contentType = 'text/html');

    public function addArquivo($caminhoArquivo = null);
    
    static public function loadMailer($args);

    public function enviar();
}