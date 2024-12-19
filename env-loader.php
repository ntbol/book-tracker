<?php
function env($key) {
    require "google-api-client/vendor/autoload.php";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    return $_ENV[$key];
}
