<?php

namespace Tests;

use Mockery;
use Raven_Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    protected function setUp()
    {
        parent::setUp();

        $this->sentry = Mockery::spy(Raven_Client::class);

        app()->instance('sentry', $this->sentry);
    }

    protected function assertExceptionSentToSentry(string $class)
    {
        $this->sentry->shouldHaveReceived('captureException')->with(Mockery::type($class));
    }

    protected function assertNoExceptionsSentToSentry()
    {
        $this->sentry->shouldNotHaveReceived('captureException');
    }
}
