<?php

namespace Tests\Fixtures;

class TestFixtures
{
    public function testDefault()
    {
        return 'default';
    }

    public function testError()
    {
        return 'error';
    }

    public function testWithArgument($arg)
    {
        return $arg;
    }
}