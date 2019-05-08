<?php

/**
 * Euromessage Client for PHP
 * @license MIT
 * @author Arda Kılıçdağı <arda@kilicdagi.com>
 * @link https://github.com/ardakilic/euromessage-php
 * @link https://arda.pw
 *
 */

namespace Euromessage;

use GuzzleHttp\Client as Guzzle;
// use GuzzleHttp\Exception\RequestException;
use Exception;

class Client
{
    private $config;
    private $client;

    /**
     * Client constructor
     * This method sets the configuration and HTTP Client variables
     * @param array $config the configuration array
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->setClient($config['endpoints']['base_uri']);
    }

    /**
     * Creates a new member to Euromessage Member Service
     * @param array $memberData the data of the member
     * @param bool $subscribeEmail Whether the member will be automatically subscribed to email lists
     * @param bool $subscribeGSM Whether the member will be automatically subscribed to GSM lists
     * @param bool $forceUpdate Whether the data will be force updated or not
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function createMember($memberData, $subscribeEmail = true, $subscribeGSM = true, $forceUpdate = true)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'ForceUpdate' => $forceUpdate,
            'DemographicData' => [],
        ];

        $dataCounter = 0;
        $demographicList = [];
        foreach ($memberData['demographic'] as $key => $value) {
            $demographicList[$dataCounter]['Key'] = $key;
            $demographicList[$dataCounter]['Value'] = $value;
            $dataCounter++;
        }

        // Make sure to add permits
        if ($subscribeEmail === true) {
            $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
            $demographicList[$dataCounter]['Value'] = 'Y';
            $dataCounter++;
        }
        if ($subscribeGSM === true) {
            $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
            $demographicList[$dataCounter]['Value'] = 'Y';
        }

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Creates a new member to Euromessage Data Warehouse Service
     * @param array $memberData the data of the member
     * @param bool $subscribeEmail Whether the member will be automatically subscribed to email lists
     * @param bool $subscribeGSM Whether the member will be automatically subscribed to GSM lists
     * @param bool $forceUpdate Whether the data will be force updated or not
     * @param bool $insertEmptyValueForNonDemographicColumns Whether the non demographic data be filled with blank string or not
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function createMemberAtWarehouse($memberData, $subscribeEmail = true, $subscribeGSM = true, $forceUpdate = true, $insertEmptyValueForNonDemographicColumns = true)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'DwTableName' => $memberData['warehouseTableName'],
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'ForceUpdate' => $forceUpdate,
            'DemographicData' => [],
            'InsertEmptyValueForNonDemographicColumns' => $insertEmptyValueForNonDemographicColumns,
        ];

        $dataCounter = 0;
        $demographicList = [];
        foreach ($memberData['demographic'] as $key => $value) {
            $demographicList[$dataCounter]['Key'] = $key;
            $demographicList[$dataCounter]['Value'] = $value;
            $dataCounter++;
        }

        // Make sure to add permits
        if ($subscribeEmail === true) {
            $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
            $demographicList[$dataCounter]['Value'] = 'Y';
            $dataCounter++;
        }
        if ($subscribeGSM === true) {
            $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
            $demographicList[$dataCounter]['Value'] = 'Y';
        }

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member_data_warehouse'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }


    /**
     * Adds a created member to specific list(s)
     * @param array $memberData the data of the member
     * @param bool $move If Move is given True, the member will be removed from the old lists which are not
     *                  specified in SendLists array. If move is given False, the member will
     *                  only be added to the specified lists.
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function addMemberToLists($memberData, $move = false)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'Move' => $move,
            'SendLists' => [],
        ];
        $lists = [];
        foreach ($memberData['lists'] as $index => $list) {
            $lists[$index]['ListName'] = $list['name'];
            $lists[$index]['GroupName'] = $list['group'];
        }

        $body['SendLists'] = array_values($lists);

        $request = $this->client->post($this->config['endpoints']['add_to_list'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Removes a created member from specific list(s)
     * @param array $memberData the data of the member
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function removeMemberFromLists($memberData)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'DeleteIfInNoList' => false,
            'SendLists' => [],
        ];
        $lists = [];
        foreach ($memberData['lists'] as $index => $list) {
            $lists[$index]['ListName'] = $list['name'];
            $lists[$index]['GroupName'] = $list['group'];
        }

        $body['SendLists'] = array_values($lists);

        // This request is actually a DELETE request on their api documentation
        // However, it gives http 405 method not allowed
        // Support department told me that it should be POST request for updating
        // ¯\_(ツ)_/¯
        $request = $this->client->post($this->config['endpoints']['remove_from_list'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Updates notification preferences of a member from Member Service
     * @param array $memberData identifier data of the member
     * @param bool $subscribeEmail The value to set email subscription preference. If true, (s)he will be notified
     * @param bool $subscribeGsm The value to set GSM subscription preference. If true, (s)he will be notified
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function updateNotificationPreferences($memberData, $subscribeEmail = true, $subscribeGsm = true)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'ForceUpdate' => true,
            'DemographicData' => [],
        ];

        $dataCounter = 0;
        $demographicList = [];
        $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeEmail === true ? 'Y' : 'N';
        $dataCounter++;
        $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeGsm === true ? 'Y' : 'N';

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Updates notification preferences of a member from Warehouse
     * @param array $memberData identifier data of the member
     * @param bool $subscribeEmail The value to set email subscription preference. If true, (s)he will be notified
     * @param bool $subscribeGsm The value to set GSM subscription preference. If true, (s)he will be notified
     * @param bool $insertEmptyValueForNonDemographicColumns Whether the non demographic data be filled with blank string or not
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function updateNotificationPreferencesAtWarehouse($memberData, $subscribeEmail = true, $subscribeGsm = true, $insertEmptyValueForNonDemographicColumns = false)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'DwTableName' => $memberData['warehouseTableName'],
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'ForceUpdate' => true,
            'DemographicData' => [],
            'InsertEmptyValueForNonDemographicColumns' => $insertEmptyValueForNonDemographicColumns,
        ];

        $dataCounter = 0;
        $demographicList = [];
        $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeEmail === true ? 'Y' : 'N';
        $dataCounter++;
        $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeGsm === true ? 'Y' : 'N';

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member_data_warehouse'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Updates notification preferences of a member from Data Warehouse Service
     * @param array $memberData identifier data of the member
     * @param bool $subscribeEmail The value to set email subscription preference. If true, (s)he will be notified
     * @param bool $subscribeGsm The value to set GSM subscription preference. If true, (s)he will be notified
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function updateNotificationPreferencesAtDataWarehouse($memberData, $subscribeEmail = true, $subscribeGsm = true)
    {
        $token = $this->getToken();

        // The headers and the body that will be sent
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'DwTableName' => $memberData['warehouseTableName'],
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
            'ForceUpdate' => true,
            'DemographicData' => [],
        ];

        $dataCounter = 0;
        $demographicList = [];
        $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeEmail === true ? 'Y' : 'N';
        $dataCounter++;
        $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeGsm === true ? 'Y' : 'N';

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member_data_warehouse'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::JSON => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['MemberId'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Gets the member's information from Euromessage Member Service
     * @param array $memberData identifier data of the member
     * @return array the Demographic data of the created member, including member ID
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function queryMemberDemography($memberData)
    {
        $token = $this->getToken();

        // The Headers and the body that will be sent
        $headers = [
            // 'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $query = [
            'Key' => $memberData['key'],
            'Value' => $memberData['value'],
        ];

        $request = $this->client->get($this->config['endpoints']['get_demographic_data'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::QUERY => $query,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['DemographicData'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Gets the member's information from Euromessage Data Warehouse Service
     * @param array $memberData identifier data of the member
     * @return array the Demographic data of the created member, including member ID
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function queryMemberDemographyFromDataWarehouse($memberData)
    {
        $token = $this->getToken();

        // The Headers and the body that will be sent
        $headers = [
            // 'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'DwTableName' => $memberData['warehouseTableName'],
            'KeyColumn' => $memberData['key'],
            'Values' => $memberData['values'],
            'PageNumber' => 1,
            'PageSize' => 1000000000000,
        ];

        $request = $this->client->get($this->config['endpoints']['get_demographic_data_warehouse'], [
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
            \GuzzleHttp\RequestOptions::BODY => $body,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['Table'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
    }

    /**
     * Method to set a new configuration on runtime
     * @param array $config the configuration data
     * @return object $this the whole instance, to support chaining
     */
    public function setConfig($config)
    {
        $this->config = $config;

        // If a new base uri parameter is provided through the configuration, we need to re-set the HTTP Client
        if (isset($config['endpoints']['base_uri'])) {
            $this->setClient($config['endpoints']['base_uri']);
        }

        return $this;
    }

    /**
     * Method to add some new configuration values on runtime
     * @param array $config the configuration values
     * @return object $this the whole instance, to support chaining
     */
    public function addConfig($config)
    {
        $this->config = array_merge($this->config, $config);

        // If a new base uri parameter is provided through the configuration, we need to re-set the HTTP Client
        if (isset($config['endpoints']['base_uri'])) {
            $this->setClient($config['endpoints']['base_uri']);
        }

        return $this;
    }

    /**
     * Gets the token to make requests
     * @return string the token to make further requests
     * @throws Exception the exception from the request or Guzzle Client
     */
    private function getToken()
    {
        // The headers and body that will be sent to get the token
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $body = [
            'UserName' => $this->config['username'],
            'Password' => $this->config['password'],
        ];
        //try {
        $request = $this->client->post($this->config['endpoints']['get_token'], [
            \GuzzleHttp\RequestOptions::JSON => $body,
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
        ]);

        $response = json_decode($request->getBody()->getContents(), true);
        if ($request->getStatusCode() === 200) {
            if ($response['Success'] === true) {
                return $response['ServiceTicket'];
            }
        }
        throw new Exception($response['errors'][0]['Message'], $response['errors'][0]['Code']);
        //} catch (RequestException $e) {
        //
        //};
    }

    /**
     * Method to set the Guzzle HTTP Client
     * @param string $baseUri The base uri parameter of the Euromessage API
     * @return void
     */
    private function setClient($baseUri)
    {
        $this->client = new Guzzle(['base_uri' => $baseUri]);
    }
}
