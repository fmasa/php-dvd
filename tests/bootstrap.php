<?php

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die(
        "\n[ERROR] You need to run composer before running the test suite.\n" .
        "To do so run the following commands:\n" .
        "    curl -s http://getcomposer.org/installer | php\n" .
        "    php composer.phar install\n\n"
    );
}

require_once __DIR__ . '/../vendor/autoload.php';

\DVD\DVD::turnOn();
\DVD\DVD::turnOff();
