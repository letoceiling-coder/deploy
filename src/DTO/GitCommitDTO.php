<?php

namespace LetoceilingCoder\Deploy\DTO;

readonly class GitCommitDTO
{
    public function __construct(
        public string $message,
        public bool $allowEmpty = false
    ) {
    }
}

