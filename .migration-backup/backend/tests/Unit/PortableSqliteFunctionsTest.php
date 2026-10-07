<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Helpers\PortableSql;
use App\Helpers\PortableSqliteFunctions;
use App\Traits\ByLocation;
use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit\Framework\TestCase;

class PortableSqliteFunctionsTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        PortableSqliteFunctions::register($this->pdo);
    }

    public function test_field_function_preserves_mysql_order_and_unmatched_values(): void
    {
        $result = $this->pdo->query(
            "SELECT FIELD('ready', 'new', 'ready', 'delivered') AS matched, "
            . "FIELD('missing', 'new', 'ready') AS unmatched, "
            . 'FIELD(NULL, NULL) AS null_value'
        )->fetch(PDO::FETCH_ASSOC);

        $this->assertSame(2, (int) $result['matched']);
        $this->assertSame(0, (int) $result['unmatched']);
        $this->assertSame(0, (int) $result['null_value']);
    }

    public function test_bound_case_ordering_executes_in_native_sqlite(): void
    {
        [$ordering, $bindings] = PortableSql::orderedValues(
            'id',
            [30, 10, 20],
            'asc'
        );
        $statement = $this->pdo->prepare(
            "SELECT id FROM (SELECT 10 AS id UNION ALL SELECT 20 UNION ALL SELECT 30) AS records ORDER BY $ordering"
        );
        $this->executeUsingLaravelBindingTypes($statement, $bindings);

        $this->assertSame([30, 10, 20], array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN)));
    }

    public function test_bound_case_ordering_handles_zero_mixed_ids_and_unmatched_values(): void
    {
        $this->pdo->exec('CREATE TEMPORARY TABLE ordering_records (id INTEGER NOT NULL)');
        $this->pdo->exec('INSERT INTO ordering_records (id) VALUES (0), (10), (20), (30), (40)');
        [$ordering, $bindings] = PortableSql::orderedValues('id', [30, '10', 0], 'asc');
        $statement = $this->pdo->prepare(
            "SELECT id FROM ordering_records ORDER BY $ordering, id ASC"
        );
        $this->executeUsingLaravelBindingTypes($statement, $bindings);

        $this->assertSame(
            [20, 40, 30, 10, 0],
            array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN))
        );
    }

    public function test_laravel_connection_does_not_open_sqlite_until_its_first_query(): void
    {
        $writerOpened = false;
        $readerOpened = false;
        $connection = new SQLiteConnection(
            static function () use (&$writerOpened): PDO {
                $writerOpened = true;

                return new PDO('sqlite::memory:', null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            },
            ':memory:',
            '',
            ['driver' => 'sqlite']
        );
        $connection->setReadPdo(
            static function () use (&$readerOpened): PDO {
                $readerOpened = true;

                return new PDO('sqlite::memory:', null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            }
        );

        PortableSqliteFunctions::registerBeforeExecution($connection);

        $this->assertFalse($writerOpened || $readerOpened, 'Registering the connection listener must not open a database.');
        $connection->pretend(fn () => $connection->select("SELECT FIELD('ready', 'new', 'ready') AS rank"));
        $this->assertFalse($writerOpened || $readerOpened, 'A pretend query must not open the database.');
        $this->assertSame(
            2,
            (int) $connection->selectOne("SELECT FIELD('ready', 'new', 'ready') AS rank")->rank
        );
        $this->assertFalse($writerOpened, 'A read query must not open an unused write connection.');
        $this->assertTrue($readerOpened, 'Portable functions must be registered on the actual read PDO.');
    }

    public function test_st_distance_sphere_computes_real_great_circle_distances(): void
    {
        $statement = $this->pdo->query(
            'SELECT ST_Distance_Sphere(POINT(0, 0), POINT(0, 0)) AS same_point, '
            . 'ST_Distance_Sphere(POINT(0, 0), POINT(1, 0)) AS one_degree_equator, '
            . 'ST_Distance_Sphere(POINT(0, 0), POINT(0, 1)) AS one_degree_meridian, '
            . 'ST_Distance_Sphere(POINT(NULL, NULL), POINT(0, 0)) AS missing_point'
        );
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $this->assertSame(0.0, (float) $result['same_point']);
        $this->assertEqualsWithDelta(111194.68, (float) $result['one_degree_equator'], 0.1);
        $this->assertEqualsWithDelta(111194.68, (float) $result['one_degree_meridian'], 0.1);
        $this->assertNull($result['missing_point']);
    }

    public function test_distance_query_uses_native_sqlite_point_functions(): void
    {
        $expression = PortableSql::distanceKilometers(
            'shops.longitude',
            'shops.latitude',
            -73.9857,
            40.7484,
            'sqlite'
        );

        $statement = $this->pdo->query(
            "SELECT $expression AS distance FROM (SELECT -73.9857 AS longitude, 40.7484 AS latitude) AS shops"
        );

        $this->assertSame(0.0, (float) $statement->fetchColumn());
    }

    public function test_location_distance_uses_matching_branch_and_accepts_zero_coordinates(): void
    {
        $this->pdo->exec(
            'CREATE TABLE shops (id INTEGER, latitude REAL, longitude REAL)'
        );
        $this->pdo->exec(
            'CREATE TABLE shop_locations ('
            . 'id INTEGER, shop_id INTEGER, region_id INTEGER, country_id INTEGER, city_id INTEGER, '
            . 'area_id INTEGER, type INTEGER, latitude REAL, longitude REAL)'
        );
        $this->pdo->exec('INSERT INTO shops VALUES (1, 10, 10)');
        $this->pdo->exec('INSERT INTO shop_locations VALUES (1, 1, NULL, NULL, 5, NULL, 2, 0, 1)');

        $locationQuery = new class {
            use ByLocation;
        };

        $this->assertTrue($locationQuery->hasValidCoordinates(0, 0));
        $distance = $locationQuery->distanceSelectRaw(['city_id' => 5], 0.0, 0.0, 'sqlite');
        $statement = $this->pdo->query("SELECT $distance AS distance FROM shops WHERE id = 1");

        $this->assertEqualsWithDelta(111.2, (float) $statement->fetchColumn(), 0.1);
    }

    public function test_sqlite_json_number_ordering_keeps_delivery_time_numeric(): void
    {
        $expression = PortableSql::jsonNumber('delivery_time', 'from', 'sqlite');
        $statement = $this->pdo->query(
            "SELECT $expression FROM (SELECT '{\"from\":\"90\"}' AS delivery_time)"
        );

        $this->assertSame(90.0, (float) $statement->fetchColumn());
    }

    /**
     * Mirror Illuminate\Database\Connection::bindValues() for raw PDO queries.
     *
     * @param list<mixed> $bindings
     */
    private function executeUsingLaravelBindingTypes(\PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $index => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_resource($value) => PDO::PARAM_LOB,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue($index + 1, $value, $type);
        }

        $statement->execute();
    }
}