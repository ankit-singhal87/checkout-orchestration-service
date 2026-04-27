<?php

declare(strict_types=1);

namespace App\Application\Checkout\Simulation;

/**
 * Outcome for deterministic local checkout simulators.
 */
final readonly class CheckoutSimulationResult
{
    private function __construct(
        public bool $successful,
        public string $reason,
    ) {}

    /**
     * Build a successful simulator result.
     */
    public static function succeeded(): self
    {
        return new self(true, 'simulated_success');
    }

    /**
     * Build a failed simulator result.
     */
    public static function failed(string $reason): self
    {
        return new self(false, $reason);
    }
}
