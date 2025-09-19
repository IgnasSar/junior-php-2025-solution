<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for a collection of IP addresses.
 * Used in controller methods that handle bulk IP requests.
 *
 * Validation rules:
 * - The array cannot be empty.
 * - Each IP must be non-empty and a valid address.
 */
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
