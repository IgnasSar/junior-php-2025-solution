<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\IpAddress;

class IpAddressNormalizer
{
    public function normalize(IpAddress $ipAddress): array
    {
        return [
            'id' => $ipAddress->getId(),
            'data' => $ipAddress->getData()
        ];
    }
}
