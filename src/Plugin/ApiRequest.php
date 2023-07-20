<?php

namespace App\Plugin;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiRequest
{
    private string $url = '';

    private $body = '';

    private $method = self::HTTP_METHOD_GET;

    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public const HTTP_METHOD_PUT = 'PUT';

    private int $responseStatusCode;
    private string $responseContentType;
    private array $responseMessage;

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }
    
    public function setRequestUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getRequestUrl(): string
    {
        return $this->url;
    }

    public function setRequestBody($body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getRequestBody(): array
    {
        $body = [
            'url'       => $this->url,
            'method'    => $this->method,
            'body'      => $this->body
        ];
        return $body;
    }

    public function setRequestMethod($method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getRequestMethod(): string
    {
        return $this->method;
    }

    public function sendRequest(): bool
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiI1NWEwNmZmOC1hMTkwLTQzMjItODM0NC01OWM2NThjOTg0ODUiLCJzY29wZSI6Imh0dHBzOi8vd2VzdGV4LmluY2lkZW50aXEuY29tIiwic3ViIjoiMzI1Yzg2MDMtYzI3ZC00NGM5LTk4ODgtYjZkMjRlYTJjNWY1IiwianRpIjoiM2VkYTE0NWUtMDAyNy1lZTExLWE5YmItMDAwZDNhZTUwNWYwIiwiaWF0IjoxNjg5ODU5MzA3LjE4MywiZXhwIjoxNzg0NTUzNzA3LjE5N30.VCP0nUEWiG8pmVsgEPm7nH3U44CxlmUI4cX5-4hF-so';
        $response = $this->client->request(
            $this->getRequestMethod(),
            $this->getRequestUrl(),
            [
                'auth_bearer' => $token
            ]
        );
        
        $this->responseStatusCode = $response->getStatusCode();
        $this->responseContentType = $response->getHeaders()['content-type'][0];
        $this->responseMessage = $response->toArray();

        return $this->responseStatusCode === 200 ? true : false;
    }
}