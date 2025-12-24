<?php

namespace LetoceilingCoder\Deploy\DTO;

readonly class DeployRequestDTO
{
    public function __construct(
        public string $serverUrl,
        public string $token,
        public string $branch,
        public ?string $version = null,
        public bool $withSeed = false,
        public bool $insecure = false,
        public int $timeout = 300
    ) {
    }
}

