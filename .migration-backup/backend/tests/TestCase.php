<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * RefreshDatabase (used by most Feature tests) runs migrate:fresh -
     * dropping every table - the instant any test using it starts. The
     * only thing meant to stand between that and the real application
     * database is .env.testing existing and pointing DB_DATABASE somewhere
     * isolated (see docs/testing-database.md). This is a hard, independent
     * backstop for exactly that gap: overriding setUpTraits() runs this
     * check *before* Laravel wires up any test trait, including
     * RefreshDatabase, so it can veto the wipe before it happens rather
     * than after - a missing/misconfigured .env.testing, a stale cached
     * config (bootstrap/cache/config.php from a prior config:cache), or a
     * copy-paste mistake all get caught here instead of silently dropping
     * production tables.
     */
    protected function setUpTraits()
    {
        $this->guardAgainstRunningTestsOnANonTestDatabase();

        return parent::setUpTraits();
    }

    private function guardAgainstRunningTestsOnANonTestDatabase(): void
    {
        $database = (string) DB::connection()->getDatabaseName();

        if (!str_contains($database, 'test')) {
            throw new RuntimeException(
                "Refusing to run tests: the resolved database is [$database], whose name " .
                "doesn't contain \"test\". This almost always means .env.testing is missing, " .
                "misconfigured, not loaded, or shadowed by a stale bootstrap/cache/config.php " .
                "(run `php artisan config:clear` if one exists) - and the test suite (which " .
                "runs migrate:fresh, dropping every table) is about to run against a real " .
                "database instead of an isolated one. See docs/testing-database.md."
            );
        }
    }
}
