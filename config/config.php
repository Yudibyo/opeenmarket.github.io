<?php
// Menggunakan MongoDB Driver
try {
    // Koneksi ke MongoDB
    $mongoClient = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $dbName = "opeenmarket_db";
} catch (MongoDB\Driver\Exception\Exception $e) {
    die("Koneksi ke MongoDB gagal: " . $e->getMessage());
}
?>
