<?php

namespace App\Plugin;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiRequest
{
    private array $config;
    private string $url = '';

    private string|array|null $body = null;

    private bool $bodyIsJson = false;

    private $method = self::HTTP_METHOD_GET;

    public const HTTP_METHOD_GET = 'GET';
    public const HTTP_METHOD_POST = 'POST';
    public const HTTP_METHOD_DELETE = 'DELETE';
    public const HTTP_METHOD_PUT = 'PUT';

    private int $responseStatusCode = 0;
    private string $responseContentType = '';
    private array $responseMessage = [];

    public function __construct(
        private readonly HttpClientInterface $client,
        array $config
    ) {
        $this->config = $config;
    }
    
    protected function setRequestUrl(string $url): self
    {
        $this->url = $this->config['plugin']['api_url'] . '/' . ltrim($url, '/');
        return $this;
    }

    protected function getRequestUrl(): string
    {
        return $this->url;
    }

    protected function setRequestBody(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    protected function setRequestBodyAsJson(array $body): self
    {
        $this->bodyIsJson = true;
        $this->setRequestBody($body);
        return $this;
    }

    protected function getRequestBody()
    {
        return $this->body;
    }

    protected function getRequest(): array
    {
        $body = [
            'url'       => $this->url,
            'method'    => $this->method,
        ];
        if ($this->bodyIsJson) {
            $body['json'] = $this->body;
        } else {
            $body['body'] = $this->body;
        }
        return $body;
    }

    protected function setRequestMethod($method): self
    {
        $this->method = $method;
        return $this;
    }

    protected function getRequestMethod(): string
    {
        return $this->method;
    }

    protected function sendRequest(): ?object
    {
        $options = [
            'auth_bearer' => $this->config['plugin']['api_token'],
        ];

        if ($this->getRequestBody()) {
            if ($this->bodyIsJson) {
                $options['json'] = $this->getRequestBody();
            } else {
                $options['body'] = $this->getRequestBody();
            }
        }

        $response = $this->client->request(
            $this->getRequestMethod(),
            $this->getRequestUrl(),
            $options
        );
        
        $this->responseStatusCode = $response->getStatusCode();
        $this->responseContentType = $response->getHeaders()['content-type'][0];
        $this->responseMessage = $response->toArray();

        return $this->responseStatusCode === 200 ? $this : null;
    }

    protected function getStatusCode(): int
    {
        return $this->responseStatusCode;
    }

    protected function getContentType(): string
    {
        return $this->responseContentType;
    }

    protected function getResponse(): array
    {
        return $this->getResponseItems();
    }

    /** The following might be specific to IIQ, will have to check */
    protected function getResponseCount(): ?int
    {
        return (isset($this->responseMessage['Paging']['TotalRows'])) ? $this->responseMessage['Paging']['TotalRows'] : null;
    }

    protected function getTotalPageCount(): ?int
    {
        return (isset($this->responseMessage['Paging']['PageCount'])) ? $this->responseMessage['Paging']['PageCount'] : null;
    }

    protected function getPageSize(): ?int
    {
        return (isset($this->responseMessage['Paging']['PageSize'])) ? $this->responseMessage['Paging']['PageSize'] : null;
    }

    protected function getPageIndex(): ?int
    {
        return (isset($this->responseMessage['Paging']['PageIndex'])) ? $this->responseMessage['Paging']['PageIndex'] : null;
    }

    protected function getExecutionTime(): ?int
    {
        return (isset($this->responseMessage['ExecutionTime'])) ? $this->responseMessage['ExecutionTime'] : null;
    }

    protected function getResponseItems(): array
    {
        return isset($this->responseMessage['Items']) ? $this->responseMessage['Items'] : $this->responseMessage['Item'];
    }
}