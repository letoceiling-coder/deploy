<?php

namespace LetoceilingCoder\Deploy\DTO;

readonly class DeployOptionsDTO
{
    public function __construct(
        public ?string $message = null,
        public bool $skipBuild = false,
        public bool $dryRun = false,
        public bool $insecure = false,
        public bool $withSeed = false,
        public ?string $branch = null,
        public ?string $version = null
    ) {
    }
}

