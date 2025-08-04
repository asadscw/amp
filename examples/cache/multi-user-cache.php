#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use Amp\Loop;
use function Amp\delay;
use function Amp\call;
use function Amp\Promise\all;

final class MultiUserCache
{
    private $storage = [];

    public function set(string $user, string $key, $value): \Amp\Promise
    {
        return call(function () use ($user, $key, $value) {
            // Simulate I/O latency.
            yield delay(10);
            $this->storage[$user][$key] = $value;
        });
    }

    public function get(string $user, string $key): \Amp\Promise
    {
        return call(function () use ($user, $key) {
            yield delay(10);
            return $this->storage[$user][$key] ?? null;
        });
    }
}

Loop::run(function () {
    $cache = new MultiUserCache();

    // Store some values for multiple users concurrently.
    yield all([
        'alice' => $cache->set('alice', 'balance', 100),
        'bob'   => $cache->set('bob', 'balance', 50),
        'carol' => $cache->set('carol', 'balance', 75),
    ]);

    // Retrieve the values concurrently.
    $balances = yield all([
        'alice' => $cache->get('alice', 'balance'),
        'bob'   => $cache->get('bob', 'balance'),
        'carol' => $cache->get('carol', 'balance'),
    ]);

    foreach ($balances as $user => $balance) {
        echo ucfirst($user) . " balance: {$balance}" . PHP_EOL;
    }
});
