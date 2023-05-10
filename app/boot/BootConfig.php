<?php

namespace Boot;

use PHPMailer\PHPMailer\SMTP;
use Boot\Provider\Doctrine\DoctrineProvider;
use Core\Controller\Provider\BaseControllerProvider;
use Boot\Provider\{HandlerErroProvider, JwtProvider, PHPmailerProvider, TwigProvider};

class BootConfig
{
    static public function listProvider($debug = true)
    {
        return [
//            HandlerErroProvider::class => (object)[
//                'displayErros'    => $debug,
//                'logErros'        => false,
//                'logErrosDetails' => false
//            ],
            DoctrineProvider::class    => (object)[
                'conn' => [
                    'host'     => 'localhost',
                    'dbname'   => 'social_niveis',
                    'user'     => 'root',
                    'password' => ''
                ]
            ],
            TwigProvider::class        => (object)[
                'debug' => $debug
            ],
            PHPmailerProvider::class   => (object)[
                // 'SMTPDebug'  => SMTP::DEBUG_SERVER,
                // 'host'       => 'mail.itwebagenciadigital.com',
                // 'username'   => 'mailer-sender@itwebagenciadigital.com',
                // 'password'   => 'HxdPTmPd)p{t',
                // 'port'       => PHPmailerProvider::PORT_SSL,
                // 'encryption' => PHPMailer::ENCRYPTION_SMTPS,
                // 'SMTPAuth'   => true,
                'SMTPDebug'  => SMTP::DEBUG_OFF,
                'host'       => 'smtp.mailtrap.io',
                'username'   => 'eb669ba0ae6f06',
                'password'   => 'f48244dbd4201c',
                'port'       => 2525,
                'SMTPAuth'   => true,
            ],
            JwtProvider::class         => (object)[
                'life' => 999999,
                'life_public' => 999999,
                'life_refresh' => 999999,
            ],
            BaseControllerProvider::class => (object)[],
        ];
    }
}
