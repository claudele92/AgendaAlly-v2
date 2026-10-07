<?php
declare(strict_types=1);
namespace Tests\Hardening;

use Database\Seeders\DevelopmentServicePhotosSeeder as Photos;
use PHPUnit\Framework\TestCase;

final class NativeServicePhotoSeedTest extends TestCase
{
    public function test_reference_is_native_deterministic_and_content_versioned(): void
    {
        $legacy = Photos::mediaReference(25, 505);
        $final = Photos::mediaReference(25, 505, hash('sha256', 'reviewed'));
        self::assertSame($final, Photos::mediaReference(25, 505, hash('sha256', 'reviewed')));
        self::assertNotSame($legacy, $final);
        self::assertMatchesRegularExpression('#^/storage/images/services/shops/505/1000000001-[a-f0-9-]{36}\.jpg$#', $final);
        self::assertNotSame($final, Photos::mediaReference(25, 505, hash('sha256', 'different')));
    }

    public function test_only_known_demo_reference_and_bytes_can_be_replaced(): void
    {
        self::assertTrue(Photos::isKnownDemoMedia('/legacy', '/icon', '/legacy', '/final', 'old', 'old', 'new'));
        self::assertTrue(Photos::isKnownDemoMedia('/final', '/icon', '/legacy', '/final', 'new', 'old', 'new'));
        self::assertFalse(Photos::isKnownDemoMedia('/legacy', '/icon', '/legacy', '/final', 'vendor-bytes', 'old', 'new'));
        self::assertFalse(Photos::isKnownDemoMedia('/final', '/icon', '/legacy', '/final', 'vendor-bytes', 'old', 'new'));
        self::assertFalse(Photos::isKnownDemoMedia('/vendor-photo', '/icon', '/legacy', '/final', 'old', 'old', 'new'));
        self::assertFalse(Photos::isKnownDemoMedia(null, '/icon', '/legacy', '/final', null, 'old', 'new'));
    }

    public function test_already_correct_media_and_original_catalog_references_are_idempotent(): void
    {
        self::assertTrue(Photos::isKnownDemoMedia('/icon', '/icon', '/legacy', '/final', null, 'old', 'new'));
        self::assertTrue(Photos::isKnownDemoMedia('/legacy', '/icon', '/legacy', '/legacy', 'old', 'old', 'old'));
        self::assertFalse(Photos::isKnownDemoMedia('/legacy', '/icon', '/legacy', '/final', null, 'old', 'new'));
    }
}