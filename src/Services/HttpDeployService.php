<?php

namespace LetoceilingCoder\Deploy\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use LetoceilingCoder\Deploy\Contracts\HttpDeployServiceInterface;
use LetoceilingCoder\Deploy\Contracts\LoggerServiceInterface;
use LetoceilingCoder\Deploy\DTO\DeployRequestDTO;
use LetoceilingCoder\Deploy\Exceptions\ConfigurationException;
use LetoceilingCoder\Deploy\Exceptions\HttpDeployException;

class HttpDeployService implements HttpDeployServiceInterface
{
    private Client $httpClient;

    public function __construct(
        private LoggerServiceInterface $logger
    ) {
        $this->httpClient = new Client([
            'timeout' => 300,
            'connect_timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function validateConfiguration(): bool
    {
        $serverUrl = env('DEPLOY_SERVER_URL');
        $token = env('DEPLOY_TOKEN');

        if (empty($serverUrl)) {
            throw new ConfigurationException('DEPLOY_SERVER_URL is not set in .env');
        }

        if (empty($token)) {
            throw new ConfigurationException('DEPLOY_TOKEN is not set in .env');
        }

        if (!filter_var($serverUrl, FILTER_VALIDATE_URL)) {
            throw new ConfigurationException('DEPLOY_SERVER_URL is not a valid URL');
        }

        return true;
    }

    public function deploy(DeployRequestDTO $dto): array
    {
        $this->logger->step('HTTP', "Sending deploy request to: {$dto->serverUrl}");

        try {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $dto->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'branch' => $dto->branch,
                    'version' => $dto->version,
                    'with_seed' => $dto->withSeed,
                ],
                'timeout' => $dto->timeout,
            ];

            // Disable SSL verification if insecure flag is set
            if ($dto->insecure) {
                $options['verify'] = false;
                $this->logger->warning('SSL verification disabled (--insecure flag)');
            }

            // Mask token in logs (show only first 4 and last 4 characters)
            $maskedToken = $this->maskToken($dto->token);
            $this->logger->debug('Deploy request prepared', [
                'url' => $dto->serverUrl,
                'branch' => $dto->branch,
                'version' => $dto->version,
                'token' => $maskedToken,
            ]);

            $response = $this->httpClient->post($dto->serverUrl, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $this->logger->step('HTTP', "Response status: {$statusCode}");

            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logger->info('Deploy request successful', [
                    'status' => $statusCode,
                    'response' => $data,
                ]);

                return [
                    'success' => true,
                    'status' => $statusCode,
                    'data' => $data,
                ];
            }

            $this->logger->error('Deploy request failed', [
                'status' => $statusCode,
                'response' => $data,
            ]);

            throw new HttpDeployException(
                "Deploy request failed with status {$statusCode}: " . ($data['message'] ?? $body)
            );

        } catch (GuzzleException $e) {
            $this->logger->error('HTTP request exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            throw new HttpDeployException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Mask token for logging (show only first 4 and last 4 characters)
     */
    private function maskToken(string $token): string
    {
        $length = strlen($token);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($token, 0, 4) . str_repeat('*', $length - 8) . substr($token, -4);
    }
}

