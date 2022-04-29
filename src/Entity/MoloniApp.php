<?php

namespace Moloni\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

class MoloniApp
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_id", type="string" , length=250)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=250)
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=250)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="refresh_token", type="string", length=250)
     */
    private $refreshToken;

    /**
     * @var int
     *
     * @ORM\Column(name="company_id", type="integer")
     */
    private $companyId;

    /**
     * @var int
     *
     * @ORM\Column(name="login_date", type="integer", length=250)
     */
    private $loginDate;

    /**
     * @var string
     *
     * @ORM\Column(name="access_time", type="string", length=250)
     */
    private $accessTime;

    /**
     * Verifies access token based on date
     *
     * @return bool
     */
    public function isValidAccessToken(): bool
    {
        return strtotime('+13 days', $this->accessTime) > time();
    }

    /**
     * Verifies refresh token based on date
     *
     * @return bool
     */
    public function isValidRefreshToken(): bool
    {
        return strtotime('+40 minutes', $this->accessTime) > time();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccessTime(): string
    {
        return $this->accessTime;
    }

    /**
     * @param string $accessTime
     */
    public function setAccessTime(string $accessTime): void
    {
        $this->accessTime = $accessTime;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    /**
     * @param int $companyId
     */
    public function setCompanyId(int $companyId): void
    {
        $this->companyId = $companyId;
    }

    /**
     * @return int
     */
    public function getLoginDate(): int
    {
        return $this->loginDate;
    }

    /**
     * @param int $loginDate
     */
    public function setLoginDate(int $loginDate): void
    {
        $this->loginDate = $loginDate;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'company_id' => $this->getCompanyId(),
            'login_date' => $this->getLoginDate(),
            'access_time' => $this->getAccessTime(),
        ];
    }
}
