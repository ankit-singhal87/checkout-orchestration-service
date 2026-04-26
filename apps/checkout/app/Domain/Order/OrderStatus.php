<?php

declare(strict_types=1);

namespace App\Domain\Order;

/**
 * Order states used by the simulated confirmation flow.
 */
enum OrderStatus: string
{
    case Confirmed = 'confirmed';
}
