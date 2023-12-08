<?php

namespace Ridouchire\RadioMetrics\Tests;

use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class DatabaseTestCase extends TestCase
{
    public Medoo $db;

    public function setUp(): void
    {
        $this->db = new Medoo([
            'type'     => 'sqlite',
            'database' => __DIR__ . '/../../test.db'
        ]);
    }
}
