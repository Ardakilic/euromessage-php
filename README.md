Euromessage-PHP
--------

This PHP library helps you to create member data, add them to specific lists or delete them, and update preferences of members on [Euromessage](https://www.euromsg.com/).

This client uses the Euromessage's REST API, and you can set the endpoints if you want from configuration.

Requirements
--------
* Not an ancient PHP version (>=5.5.0)
* ext-json

Installation
--------
You can simply install via composer package manager:

```bash
composer require ardakilic/euromessage-php
```

Example Configuration
--------

Please refer to [config.example.php](config.example.php).

The configuration parameter holds the `endpoints` section, because during my integration, the company provided me a different api endpoint (base_uri).

Examples
--------

## Creating (and Updating) a member

### a) From Member Service

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
// Whether the member will be subscribed and force updated? These parameters are `true` as default, and optional. No need to set every time.
$subscribeEmail = true;
$subscribeGSM = true;
$forceUpdate = true; // If set to true, if the key value pair matches a current member, it updates
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

### b) From Data Warehouse Service

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'warehouseTableName' => 'your_warehouse_table_name', // The name of the data warehouse table on your system
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
    'demographic' => [ // Depends on your account's configuration
        'EMAIL' => 'john@doe.com',
        'AD' => 'John',
        'SOYAD' => 'Doe',
        'GSMNO' => '532.1234567',
        // ....
    ],
];
// Whether the member will be subscribed and force updated, or will the non-demographic fields be filled with blanks? These parameters are true as default, and optional. No need to set every time.
$subscribeEmail = true;
$subscribeGSM = true;
$forceUpdate = true;  // If set to true, if the key value pair matches a current member, it updates
$insertEmptyValueForNonDemographicColumns = true;
try {
    $response = $euromessage->createMemberAtWarehouse($memberData, $subscribeEmail, $subscribeGSM, $forceUpdate, $insertEmptyValueForNonDemographicColumns);
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

## Removing a member from list(s)

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
    $response = $euromessage->removeMemberFromLists($memberData);
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

### a) From Member Service

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
try {
    $response = $euromessage->updateNotificationPreferences($memberData, $subscribeEmail, $subscribeGSM);
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

### b) From Data Warehouse Service

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'warehouseTableName' => 'your_warehouse_table_name', // The name of the data warehouse table on your system
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
];
// When these parameters are set to true, memberData wants to contact with the channels
// If these parameters are set to false, memberData wants to unsubscribe from Email or GSM
$subscribeEmail = true;
$subscribeGSM = true;
try {
    $response = $euromessage->updateNotificationPreferencesAtDataWarehouse($memberData, $subscribeEmail, $subscribeGSM);
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

**Notice:** If you have members both in Data Warehouse and Member Service, as support department confirmed, you need to run `updateNotificationPreferencesAtDataWarehouse` method. It will find and update the data both in data warehouse and the members created over default Member Service.


## Querying member's Demographic Data

### a) From Member Service

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'key' => 'KEY_ID', // Unique identifier for the service
    'value' => 'Value',
];
try {
    $response = $euromessage->queryMemberDemography($memberData);
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

### b) From Data Warehouse Service

```php
<?php
$config = require('./config.php'); // or from env, etc. Should respect the example configuration
$euromessage = new Euromessage\Client($config);
$memberData = [
    'warehouseTableName' => 'your_warehouse_table_name', // The name of the data warehouse table on your system
    'key' => 'KEY_ID', // Unique identifier for the service
    'values' => [
        'Value', // You can query multiple values with this method from warehouse, that's why this is array.
        // You can add more values like 'Value2', 'Value3' etc.
    ],
];
try {
    $response = $euromessage->queryMemberDemographyFromDataWarehouse($memberData);
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

Changelog
--------

### 0.1.0

* Adding members to Data Warehouse method added
* Querying Member ID and Demography methods added from both Member Service and Data Warehouse
* `forceUpdate` parameter removed from `updateNotificationPreferences` method

The endpoints section at configuration parameter now holds new lines, please update accordingly.

### 0.0.1

* Initial Release

License
--------
[MIT](./LICENSE)