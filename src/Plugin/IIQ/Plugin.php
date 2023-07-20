<?php

namespace App\Plugin\IIQ;

use App\Plugin\ApiRequest;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Plugin extends ApiRequest
{
    private array $config = [];

    public function __construct(
        private readonly ContainerBagInterface $params,
        private readonly HttpClientInterface $client
    )
    {
        $this->config = $this->getConfig();
        parent::__construct($this->client);
    }
    private function getConfig(): array
    {
        $config = Yaml::parseFile($this->params->get('kernel.project_dir') . '/config/plugins/iiq.yaml');
        if (!array_key_exists('plugin', $config)) {
            throw new InvalidConfigurationException('API configuration is invalid.');
        }

        if (!array_key_exists('api_url', $config['plugin'])) {
            throw new InvalidConfigurationException('API configuration is invalid.');
        }

        return $config;
    }

    public function test(): void
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiI1NWEwNmZmOC1hMTkwLTQzMjItODM0NC01OWM2NThjOTg0ODUiLCJzY29wZSI6Imh0dHBzOi8vd2VzdGV4LmluY2lkZW50aXEuY29tIiwic3ViIjoiMzI1Yzg2MDMtYzI3ZC00NGM5LTk4ODgtYjZkMjRlYTJjNWY1IiwianRpIjoiM2VkYTE0NWUtMDAyNy1lZTExLWE5YmItMDAwZDNhZTUwNWYwIiwiaWF0IjoxNjg5ODU5MzA3LjE4MywiZXhwIjoxNzg0NTUzNzA3LjE5N30.VCP0nUEWiG8pmVsgEPm7nH3U44CxlmUI4cX5-4hF-so';
        dd($this->client);
    }

    public function setRequestUrl(string $url): self
    {
        $requestUrl = $this->config['plugin']['api_url'] . '/' . ltrim($url, '/');
        parent::setRequestUrl($requestUrl);
        return $this;
    }

    private function setAuthorization()
    {
        $authorizations = 'Authorization: Bearer ' . $this->config['plugin']['api_token'];
    }
}


    