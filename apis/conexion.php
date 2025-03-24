<?php

$dotenv = parse_ini_file(__DIR__ . '/.env');

if (!$dotenv) {
    die('Error al cargar el archivo .env');
}

$conn = new mysqli(
    $dotenv['DB_HOST'],
    $dotenv['DB_USER'],
    $dotenv['DB_PASS'],
    $dotenv['DB_NAME']
);
