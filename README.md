Euromessage-PHP
--------

This PHP library helps you to create memberDatas, add them to specific lists or unsubscribe them to [Euromessage](https://www.euromsg.com/).

This client uses the Euromessage's REST API, and you can set the endpoints if you want from configuration.

Requirements
--------
* Not an ancient PHP version
* ext-json

Example Configuration
--------
```php
<?php
return [
    'username' => 'euromessage_api_username',
    'password' => 'euromessage_api_password',

    'endpoints' => [
        'base_uri' => 'http://api.relateddigital.com/resta/api/',
        'get_token' => 'auth/login',
        'create_update_member' => 'Member/InsertMemberDemography',
        'add_to_list' => 'Member/AddToSendLists',
        'remove_from_list' => 'Member/RemoveFromSendLists',
    ]
];
```

The configuration parameter holds the `endpoints` section, because during my integration, the company provided me a different api endpoint (base_uri).

Examples
--------

## Creating a member

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
    'demographic' => [ // Depends on your account's configuration
        'E-Posta' => 'john@doe.com',
        'Adınız' => 'John',
        'Soyadınız' => 'Doe',
        'Telefon' => '532.1234567',
        // ....
    ],
];
// Whether the member will be subscribed and force updated? These parameters are true as default, and optional. No need to set every yime.
$subscribeEmail = true;
$subscribeGSM = true;
$forceUpdate = true;
try {
    $response = $euromessage->createMember($memberData, $subscribeEmail, $subscribeGSM, $forceUpdate);
} catch (Exception $e) {
    if($e instanceof \GuzzleHttp\Exception\RequestException) {
        // Guzzle request exception
    } else {
        // Class's exception, wrong credentials etc.
    }
    // The code and message are according to the Euromessage API
    var_dump($e->getCode(), $e->getMessage(), $e->getTrace());
}
```

## Adding a member to list(s)

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
    'lists' => [
        [
            'name' => 'List Name 1',
            'group' => 'Group Name', // "Genel" may be set as default
        ],
    ],
];
try {
    $response = $euromessage->addMemberToLists($memberData);
} catch (Exception $e) {
    if($e instanceof \GuzzleHttp\Exception\RequestException) {
        // Guzzle request exception
    } else {
        // Class's exception, wrong credentials etc.
    }
    // The code and message are according to the Euromessage API
    var_dump($e->getCode(), $e->getMessage(), $e->getTrace());
}
```

## Update Notification Preferences of a Member

This method sets the member's preferences on his/her demographic data, so this applies to all lists, according to Euromessage's documentation.

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
];
// When these parameters are set to true, memberData wants to contact with the channels
// If these parameters are set to false, memberData wants to unsubscribe from Email or GSM
$subscribeEmail = true;
$subscribeGSM = true;
// Optional parameter for force updating
$forceUpdate = true;
try {
    $response = $euromessage->updateNotificationPreferences($memberData, $subscribeEmail, $subscribeGSM, $forceUpdate);
} catch (Exception $e) {
    if($e instanceof \GuzzleHttp\Exception\RequestException) {
        // Guzzle request exception
    } else {
        // Class's exception, wrong credentials etc.
    }
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