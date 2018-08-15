Euromessage-PHP
--------

This PHP library helps you to create users, add them to specific lists or unsubscribe them to [Euromessage](https://www.euromsg.com/).

This client uses the Euromessage's REST API, and you can set the endpoints if you want from configuration.

Requirements
--------
* Not an ancient PHP version
* ext-json

Example Configuration
--------
```
<?php
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
```

Examples
--------

## Creating a member

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$userData = [
    'key' => 'KeyID', // Unique identifier for the service
    'value' => 'Value',
    'demographic' => [ // Depends on your account's configuration
        'E-Posta' => 'john@doe.com',
        'Adınız' => 'John',
        'Soyadınız' => 'Doe',
        'Telefon' => '532.1234567',
        // ....
    ],
];
// Whether the user will be force updated? The parameter is true as default, no need to set.
$forceUpdate = true;
try {
    $response = $euromessage->createMember($userData, $forceUpdate);
} catch (Exception $e) {
    // The code and message are according to the Euromessage API
    var_dump($e->getCode(), $e->getMessage(), $e->getTrace());
}
```

## Adding the member to a list

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$userData = [
    'key' => 'KeyID', // Unique identifier for the service
    'value' => 'Value',
    'lists' => [
        [
            'name' => 'List Name 1',
            'Group' => 'Group Name', // "Genel" may be set as default
        ],
    ],
];
try {
    $response = $euromessage->createMember($userData);
} catch (Exception $e) {
    // The code and message are according to the Euromessage API
    var_dump($e->getCode(), $e->getMessage(), $e->getTrace());
}
```

TODOs
--------
There's not much todo required for my personal needs, however any pull requests will be considered and appreciated. 

License
--------
[MIT](./LICENSE)