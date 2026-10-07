<?php

namespace App\Http\Controllers;

use RuntimeException;

/**
 * Thrown when a claim fails the centralized eligibility rule while the claim
 * transaction is already open. Rolls the transaction back so no partial
 * claim/report state is persisted, and surfaces the user-facing reason.
 */
class ClaimRejectedException extends RuntimeException {}
