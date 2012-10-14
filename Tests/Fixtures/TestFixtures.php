<?php

namespace Tests\Fixtures;

class TestFixtures
{
    public function testDefault()
    {
        return true;
    }

    public function testError()
    {
        return false;
    }

    public function testWithArgument($arg)
    {
        return $arg;
    }
}