<?php

/**
 * Euromessage Client for PHP
 * @license MIT
 * @author Arda Kılıçdağı <arda@kilicdagi.com>
 * @link https://arda.pw
 *
 */

return [
    'username' => 'euromessage_api_username',
    'password' => 'euromessage_api_password',

    'endpoints' => [
        'base_uri' => 'http://api.relateddigital.com/resta/api/',
        'get_token' => 'auth/login',
        'create_member' => 'Member/InsertMemberDemography',
        'subscribe' => 'Member/AddToSendLists',
    ]
];
