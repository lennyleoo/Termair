<?php
declare(strict_types=1);
defined('TERMAIR_CONTACT_FORM') || exit('Acceso denegado');

return [
    'smtp' => [
        'host' => 'vps-2869163-x.dattaweb.com',
        'port' => 465,
        'username' => '*****',
        'password' => '*****',
        'from_name' => 'Termair S.A. - Sitio web',
        'recipient' => 'info@termair.com',
    ],
    'security' => [
        'minimum_seconds' => 3,
        'maximum_message_length' => 5000,
    ],
];
