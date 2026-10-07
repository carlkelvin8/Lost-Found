<?php

namespace App\Services;

use App\Models\ItemReport;

/**
 * Immutable outcome of a claim eligibility evaluation.
 *
 * `lostReport` is the claimant's own LOST report that is matched to the FOUND
 * report, when one exists. It is never derived from the FOUND report's reporter.
 */
final class ClaimEligibility
{
    public function __construct(
        public readonly bool $allowed,
        public readonly string $reason,
        public readonly ?string $message = null,
        public readonly ?ItemReport $lostReport = null,
    ) {}

    public static function eligible(?ItemReport $lostReport = null): self
    {
        return new self(true, 'eligible', null, $lostReport);
    }

    public static function ineligible(string $reason, string $message): self
    {
        return new self(false, $reason, $message);
    }

    public function isIneligible(): bool
    {
        return ! $this->allowed;
    }

    public function lostReportId(): ?int
    {
        return $this->lostReport?->id;
    }
}
