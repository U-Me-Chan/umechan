<?php

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PK\Posts\Post\PasswordHash;

class PasswordHashTest extends TestCase
{
    #[Test]
    public function generate(): void
    {
        $hash = PasswordHash::generate();

        $this->assertNotEquals($hash->toString(), $hash->clearPasswordToString());
    }

    #[Test]
    public function withPasswordString(): void
    {
        $hash = PasswordHash::generate('test');

        $this->assertNotNull($hash->clearPasswordToString());
        $this->assertEquals('test', $hash->clearPasswordToString());
        $this->assertEquals(hash('sha256', 'test'), $hash->toString());
        $this->assertTrue($hash->isEqualTo('test'));
    }

    #[Test]
    public function fromString(): void
    {
        $hash = PasswordHash::fromString(hash('sha256', 'test'));

        $this->assertEquals(hash('sha256', 'test'), $hash->toString());
        $this->assertTrue($hash->isEqualTo('test'));

        $this->expectException(InvalidArgumentException::class);

        $hash->clearPasswordToString();
    }
}
