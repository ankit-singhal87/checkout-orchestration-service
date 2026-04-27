<?php

declare(strict_types=1);

namespace App\Application\Checkout;

/**
 * Closed outcomes for tenant-scoped checkout confirmation attempts.
 */
enum ConfirmCheckoutStatus
{
    case Confirmed;
    case Replayed;
    case NotFound;
    case NotReady;
    case IdempotencyConflict;
}
