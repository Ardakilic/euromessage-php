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
     * @param $config array the configuration array
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new Guzzle(['base_uri' => $this->config['base_uri']]);
    }

    /**
     * Creates a new member to Euromessage Service
     * @param $userData array the data of the user
     * @param $forceUpdate boolean whether the data will be force updated or not
     * @return string the ID of the created user
     * @throws Exception the exception from the request
     */
    public function createMember($userData, $forceUpdate = true)
    {
        $token = $this->getToken();

        // The Headers and body that will be sent to get the token
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $userData['key'],
            'Value' => $userData['value'],
            'ForceUpdate' => $forceUpdate,
            'DemographicData' => [],
        ];

        foreach ($userData['demographic'] as $key => $value) {
            $body['DemographicData'][$key] = $value;
        }

        $request = $this->client->post($this->config['endpoints']['create_member'], [
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

    public function addUserToASendList($userData, $move = false) {
        $token = $this->getToken();

        // The Headers and body that will be sent to get the token
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $token,
        ];

        $body = [
            'Key' => $userData['key'],
            'Value' => $userData['value'],
            'Move' => $move,
            'SendLists' => [],
        ];

        foreach ($userData['demographic'] as $key => $value) {
            $body['DemographicData'][$key] = $value;
        }

        $request = $this->client->post($this->config['endpoints']['create_member'], [
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
     * @throws Exception the exception from the request
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
