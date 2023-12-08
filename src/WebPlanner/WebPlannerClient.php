<?php

namespace AfSoftlab\Binaroom\Door\WebPlanner;

use \curl_init;
use AfSoftlab\Binaroom\Door\Model\Estimate;

class WebPlannerClient
{
    public const VALUE_SIZE = 1;
    public const VALUE_LENGTH = 5;

    private $adminToken;

    private $config;

    private $token;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param mixed $token
     *
     * @return self
     */
    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getModel($id, $token)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$token}",
        ];

        return $this->sendGet("/api/files/{$id}", $headers);
    }

    /**
     * @param $id
     * @param $token
     * @param array $uids
     * @param int $type Size = 1, Volume = 2, Area = 3, Storage = 4, MaterialLength = 5
     *
     * @return mixed
     * @throws \Exception
     */
    public function getModelValues($id, $token, array $uids, int $type = 1)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$token}",
        ];

        $data = [];
        foreach ($uids as $uid) {
            $data[] = [
                'value' => $type,
                // 'arg' => '',
                'uid' => $uid,
                'detailed' => true,
            ];
        }

        return $this->sendPost("/api/files/{$id}/get", json_encode($data), $headers);
    }

    /**
     * @param $id
     * @param $token
     * @param array $uids
     * @param int $type Size = 1, Volume = 2, Area = 3, Storage = 4, MaterialLength = 5
     *
     * @return mixed
     * @throws \Exception
     */
    public function getModelValuesByNames($id, array $names, int $type = 1)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}",
        ];

        $data = [];
        foreach ($names as $name) {
            $data[] = [
                'value' => $type,
                'arg' => $name,
                'detailed' => true,
            ];
        }

        return $this->sendPost("/api/files/{$id}/get", json_encode($data), $headers);
    }

    /**
     * @param $id
     * @param $token
     *
     * @return Estimate\EstimateItem[]
     */
    public function getOrderEstimate($id)
    {
        $response = $this->getOrderEstimateRaw($id);
        $result = [];
        foreach ($response->elements as $item) {
            $elements = [];
            foreach ($item->elements as $subItem) {
                $elements[] = $this->buildEstimateItem($subItem);
            }

            $result[] = $this->buildEstimateItem($item, $elements);
        }

        return $result;
    }

    /**
     * @param $id
     * @param $token
     */
    public function getOrderEstimateRaw($id)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}",
        ];

        return $this->sendGet("/api/order/{$id}/estimate", $headers);
    }

    /**
     * @param $id
     */
    public function getEstimate($id)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}",
        ];

        return $this->sendGet("/api/order/{$id}/estimate", $headers);
    }

    /**
     * @param $item
     * @param array $elements
     *
     * @return Estimate\EstimateItem
     */
    private function buildEstimateItem($item, array $elements = [])
    {
        return new Estimate\EstimateItem(
            $item->name ?? '',
            $item->sku ?? '',
            $item->description ?? '',
            $item->price,
            $item->count,
            $item->unit,
            $elements,
            $item->entities,
            $item->attributes,
            $item->cost
        );
    }

    public function getModelStructure($id)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->token}",
        ];

        return $this->sendGet("/api/files/{$id}/structure", $headers);
    }

    public function authDefaultUser()
    {
        return $this->authUser($this->config->getDefaultUserId());
    }

    public function authUser($wpUserId)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$this->getAdminToken()}",
        ];
        $data = [];
        $response = $this->sendPost("/api/account/loginas/{$wpUserId}?lifeTime=60", $data, $headers);

        return $response['accessToken'];
    }

    private function getAdminToken()
    {
        if ($this->adminToken !== null) {
            return $this->adminToken;
        }

        $data = [
            'client_id' => $this->config->getClientId(),
            'grant_type' => $this->config->getGrantType(),
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
            'scope' => $this->config->getScope(),
        ];
        $response = $this->sendPost('/connect/token', $data);
        $this->adminToken = $response['access_token'];

        return $this->adminToken;
    }

    private function sendPost($url, $data = [], $headers = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->getBaseUrl() . $url,
            CURLOPT_POST => '1',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);
        if ($res === false) {
            throw new \Exception("Error send post to {$url}");
        }

        return $res;
    }

    private function sendGet($url, $headers = [])
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config->getBaseUrl() . $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $info = curl_getinfo($ch);
        if ($info['http_code'] < 200 || $info['http_code'] > 299){
            throw new \Exception("Unexpected response status from the web planner: {$info['http_code']}");
        }

        $res = json_decode($response);
        if ($res === false) {
            throw new \Exception("Error send post to {$url}");
        }

        return $res;
    }
}
