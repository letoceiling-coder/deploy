<?php

namespace LetoceilingCoder\Deploy\Contracts;

use LetoceilingCoder\Deploy\DTO\DeployRequestDTO;

interface HttpDeployServiceInterface
{
    /**
     * Send deploy request to server
     */
    public function deploy(DeployRequestDTO $dto): array;

    /**
     * Validate server URL and token
     */
    public function validateConfiguration(): bool;
}

