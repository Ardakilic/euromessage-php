<?php

/**
 * Euromessage Client for PHP
 * @license MIT
 * @author Arda Kılıçdağı <arda@kilicdagi.com>
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
     * Client constructor.
     * @param array $config the configuration array
     * @throws Exception the exception from the code block that checks the type of the $config variable
     */
    public function __construct($config)
    {
        if (!is_array($config)) {
            throw new Exception('Configuration parameter must be an array');
        }
        $this->config = $config;
        $this->client = new Guzzle(['base_uri' => $this->config['base_uri']]);
    }

    /**
     * Creates a new member to Euromessage Service
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

        // The Headers and body that will be sent to get the token
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
            \GuzzleHttp\RequestOptions::JSON => $body,
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
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

        // The Headers and body that will be sent to get the token
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
            \GuzzleHttp\RequestOptions::JSON => $body,
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
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

        // The Headers and body that will be sent to get the token
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
            \GuzzleHttp\RequestOptions::JSON => $body,
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
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
     * Updates notification preferences of a member
     * @param array $memberData identifier data of the member
     * @param bool $subscribeEmail The value to set email subscription preference. If true, (s)he will be notified
     * @param bool $subscribeGsm The value to set GSM subscription preference. If true, (s)he will be notified
     * @param bool $forceUpdate Whether the data will be force updated or not
     * @return string the ID of the created member
     * @throws Exception the exception from the request or the Guzzle Client
     */
    public function updateNotificationPreferences($memberData, $subscribeEmail = true, $subscribeGsm = true, $forceUpdate = true)
    {
        $token = $this->getToken();

        // The Headers and body that will be sent to get the token
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
        $demographicList[$dataCounter]['Key'] = 'EMAIL_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeEmail === true ? 'Y' : 'N';
        $dataCounter++;
        $demographicList[$dataCounter]['Key'] = 'GSM_PERMIT';
        $demographicList[$dataCounter]['Value'] = $subscribeGsm === true ? 'Y' : 'N';

        $body['DemographicData'] = array_values($demographicList);

        $request = $this->client->post($this->config['endpoints']['create_update_member'], [
            \GuzzleHttp\RequestOptions::JSON => $body,
            \GuzzleHttp\RequestOptions::HEADERS => $headers,
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
     * Gets the token to make requests
     * @return string the token to make further requests
     * @throws Exception the exception from the request or Guzzle Client
     */
    private function getToken()
    {
        // The Headers and body that will be sent to get the token
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
}
