<?php

namespace Boot\Provider;

use Slim\App;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use Core\Inferface\ProviderInterface;
use Boot\Provider\Email\PHPmailer\PHPmailerMessage;

class PHPmailerProvider implements ProviderInterface
{
    const HOST = 'localhost';
    const PORT_SSL = 468;
    const PORT_SSL_2 = 587;
    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_TLS = 'tls';
    const AUTH_LOGIN = 'login';
    const AUTH_PLAIN = 'plain';
    const AUTH_CRAM_MD5 = 'cram-md5';

    /**
     * @param App $app
     * @param \stdClass|null $args
     */
    static public function load(App &$app, \stdClass $args = null)
    {

        $args->SMTPDebug  = !is_null($args->SMTPDebug) ? $args->SMTPDebug : SMTP::DEBUG_SERVER;
        $args->host       = !empty($args->host) ? $args->host : self::HOST;
        $args->port       = !empty($args->port) ? $args->port : self::PORT_SSL;
        $args->SMTPAuth   = !empty($args->SMTPAuth) ? $args->SMTPAuth : true;
        $args->username   = !empty($args->username) ? $args->username : null;
        $args->password   = !empty($args->password) ? $args->password : null;
        $args->encryption = !empty($args->encryption) ? $args->encryption : PHPMailer::ENCRYPTION_SMTPS;

        $container = $app->getContainer();
        $container->set(PHPmailerMessage::class, function () use ($args) {
            return PHPmailerMessage::loadMailer($args);
        });
    }

    
}
