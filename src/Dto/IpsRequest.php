<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class IpsRequest {
    /**
     * @param string[] $ips
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Ip list cannot be empty')]
        #[Assert\All([
            new Assert\NotBlank(message: 'Ip cannot be empty'),
            new Assert\Ip(message: 'Invalid ip address: {{ value }}')
        ])]
        public readonly array $ips = []
    ){}
}
