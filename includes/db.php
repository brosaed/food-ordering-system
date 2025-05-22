<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Execute a query with parameters
 */
function query($sql, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all rows
 */
function fetchAll($sql, $params = [])
{
    return query($sql, $params)->fetchAll();
}

/**
 * Fetch a single row
 */
function fetchOne($sql, $params = [])
{
    return query($sql, $params)->fetch();
}

/**
 * Execute a query and return row count
 */
function execute($sql, $params = [])
{
    return query($sql, $params)->rowCount();
}
