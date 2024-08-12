<?php

use PHPUnit\Framework\TestCase;
use PK\Posts\Post\IsVerifyPoster;
use PK\Posts\Post\Poster;

class PosterTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestDraft
     */
    public function testDraft(string $name, string $is_verify): void
    {
        $poster = Poster::draft($name, IsVerifyPoster::tryFrom($is_verify));

        $this->assertEquals($name, $poster->poster);
        $this->assertEquals($is_verify, $poster->is_verify->value);
    }

    public function dataProviderForTestDraft(): array
    {
        return [
            [
                'foo', IsVerifyPoster::NO->value
            ],
            [
                'bar', IsVerifyPoster::YES->value
            ]
        ];
    }

    /**
     * @dataProvider dataProviderForTestFromArray
     */
    public function testFromArray(array $state): void
    {
        $poster = Poster::fromArray($state);

        $this->assertEquals($state['poster'], $poster->poster);
        $this->assertEquals($state['is_verify'], $poster->is_verify->value);
    }

    public function dataProviderForTestFromArray(): array
    {
        return [
            [
                ['poster' =>'foo', 'is_verify' => IsVerifyPoster::NO->value]
            ],
            [
                ['poster' => 'bar', 'is_verify' => IsVerifyPoster::YES->value]
            ]
        ];
    }
}
