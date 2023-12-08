<?php

namespace AfSoftlab\Binaroom\Door\WebPlanner;

class Config
{
    private $baseUrl = '';
    private $clientId = 'WebPlanner';
    private $grantType = 'password';
    private $username = '';
    private $password = '';
    private $scope = 'WebAPI offline_access openid profile roles';
    private $defaultUserId = 0;

    public function __construct(
        string $baseUrl,
        string $username,
        string $password,
        ?int $defaultUserId = null,
        ?string $clientId = null,
        ?string $grantType = null,
        ?string $scope = null,
    ) {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->defaultUserId = $defaultUserId;

        if ($clientId) {
            $this->clientId = $clientId;
        }
        if ($grantType) {
            $this->grantType = $grantType;
        }
        if ($scope) {
            $this->scope = $scope;
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @return string|null
     */
    public function getGrantType(): ?string
    {
        return $this->grantType;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return int|null
     */
    public function getDefaultUserId(): ?int
    {
        return $this->defaultUserId;
    }
}
