<?php

namespace App\Services;

class Api
{
    private string $baseUrl;
    private string $idProperty;
    private ?string $dataKey;
    private ?string $nextPageKey;
    private int $timeout;
    private array $headers;
    private string $httpMethod;
    private array $queryParams;

    public function __construct(
        string $baseUrl,
        string $idProperty,
        ?string $dataKey = null,
        ?string $nextPageKey = null,
        int $timeout = 30,
        array $headers = [],
        string $httpMethod = 'GET',
        array $queryParams = []
    ) {
        $this->baseUrl = $baseUrl;
        $this->idProperty = $idProperty;
        $this->dataKey = $dataKey;
        $this->nextPageKey = $nextPageKey;
        $this->timeout = $timeout;
        $this->headers = $headers;
        $this->httpMethod = strtoupper($httpMethod);
        $this->queryParams = $queryParams;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getIdProperty(): string
    {
        return $this->idProperty;
    }

    public function getDataKey(): ?string
    {
        return $this->dataKey;
    }

    public function getNextPageKey(): ?string
    {
        return $this->nextPageKey;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
}
