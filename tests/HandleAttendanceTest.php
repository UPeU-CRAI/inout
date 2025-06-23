<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions/Attendance.php';

class HandleAttendanceTest extends TestCase
{
    private PDO $db;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->exec("CREATE TABLE inout (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cardnumber TEXT,
            entry INTEGER,
            exit INTEGER,
            status TEXT
        )");
    }

    public function testSuccessiveScansAlternateStatus(): void
    {
        $start = 1000;
        $this->assertSame('IN', handleAttendance($this->db, 'A1', $start));
        $this->assertSame('OUT', handleAttendance($this->db, 'A1', $start + 10));
        $this->assertSame('IN', handleAttendance($this->db, 'A1', $start + 20));
        $this->assertSame('OUT', handleAttendance($this->db, 'A1', $start + 30));

        $stmt = $this->db->query('SELECT status FROM inout ORDER BY id');
        $statuses = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame(['OUT', 'OUT'], $statuses);
    }
}
