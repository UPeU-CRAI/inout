<?php
/**
 * Minimal attendance handling using a simple IN/OUT toggle.
 * This helper works with PDO and expects a table called `inout` with
 * columns: id INTEGER PRIMARY KEY AUTOINCREMENT, cardnumber TEXT,
 * entry INTEGER, exit INTEGER, status TEXT.
 */
function handleAttendance(PDO $db, string $cardNumber, int $timestamp = null): string
{
    $timestamp = $timestamp ?? time();

    // Get last record for this card number
    $stmt = $db->prepare('SELECT * FROM inout WHERE cardnumber = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$cardNumber]);
    $last = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$last || $last['status'] === 'OUT') {
        // New entry
        $insert = $db->prepare('INSERT INTO inout(cardnumber, entry, status) VALUES (?, ?, "IN")');
        $insert->execute([$cardNumber, $timestamp]);
        return 'IN';
    }

    // Last status is IN
    $entryTime = (int)$last['entry'];
    if ($timestamp - $entryTime >= 10) {
        $update = $db->prepare('UPDATE inout SET exit = ?, status = "OUT" WHERE id = ?');
        $update->execute([$timestamp, $last['id']]);
        return 'OUT';
    }

    // Ignore if scanned again too quickly
    return $last['status'];
}
