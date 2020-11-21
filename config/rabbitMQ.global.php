<?php

declare(strict_types=1);

return [
    'rabbitMQ' => [
        'host' => getenv('RABBITMQ_HOST'),
        'port' => 5672,
        'username' => getenv('RABBITMQ_USER'),
        'password' => getenv('RABBITMQ_PASSWORD'),
    ],
];