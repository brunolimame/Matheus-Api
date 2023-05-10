<?php

namespace Boot\Provider;

use Boot\Provider\Email\Swiftmailer\SwiftmailerMessage;
use Core\Inferface\ProviderInterface;
use Slim\App;

class SwiftmailerProvider implements ProviderInterface
{
    const HOST = 'localhost';
    const PORT_SSL = 468;
    const PORT_SSL_2 = 587;
    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_TLS = 'tls';
    const AUTH_LOGIN = 'login';
    const AUTH_PLAIN = 'plain';
    const AUTH_CRAM_MD5 = 'cram-md5';

    /** @var \Swift_Mailer */
    public $mailer;
    /** @var SwiftmailerMessage */
    public $mensagem;

    /**
     * @param App $app
     * @param \stdClass|null $args
     */
    static public function load(App &$app, \stdClass $args = null)
    {

        $args->host           = !empty($args->host) ? $args->host : self::HOST;
        $args->port           = !empty($args->port) ? $args->port : self::PORT_SSL;
        $args->username       = !empty($args->username) ? $args->username : null;
        $args->password       = !empty($args->password) ? $args->password : null;
        $args->encryption     = !empty($args->encryption) ? $args->encryption : SwiftmailerProvider::ENCRYPTION_SSL;
        $args->auth_mode      = !empty($args->auth_mode) ? $args->auth_mode : SwiftmailerProvider::AUTH_LOGIN;
        $args->stream_options = !empty($args->stream_options) ? $args->stream_options : [];

        $container = $app->getContainer();
        $container->set('enviar_por_email', function () use ($args) {
            return self::loadMailer($args);
        });
    }

    static public function loadMailer($args)
    {
        $transport = new \Swift_SmtpTransport($args->host, $args->port, $args->encryption);
        empty($args->username) ?: $transport->setUsername($args->username);
        empty($args->password) ?: $transport->setPassword($args->password);
        empty($args->auth_mode) ?: $transport->setAuthMode($args->auth_mode);
        empty($args->stream_options) ?: $transport->setStreamOptions($args->stream_options);

        $mailer         = new static();
        $mailer->mailer = new \Swift_Mailer($transport);
        $logger         = new \Swift_Plugins_Loggers_ArrayLogger();
        $mailer->mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
        $mailer->mensagem = SwiftmailerMessage::newInstance();
        return $mailer;
    }


    public function enviar()
    {
        try {
            return $this->mailer->send($this->mensagem);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Erro ao enviar mensagem: [ERROR] %s", $e->getMessage()));
        }
    }
}
