<?php
declare(strict_types=1);
namespace App\Services\ManualFinance;

/** An intentional transaction rollback makes a successful command replay strictly read-only. */
final class ReplayedCommand extends \RuntimeException
{
    public function __construct(public readonly array $result) { parent::__construct('Committed command replay.'); }
}
