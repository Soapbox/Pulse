<?php

namespace Tests\Unit\Console\Commands;

use App\Heartbeat;
use Tests\TestCase;

class DeleteHeartbeatTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_not_delete_the_heartbeat_if_you_do_not_confirm()
    {
        $heartbeat = factory(Heartbeat::class)->create();
        $command = $this->artisan('heartbeat:delete', ['name' => $heartbeat->name]);

        $command->expectsQuestion('Would you like to delete this heartbeat?', false);
        $command->expectsOutput("Heartbeat not deleted.");

        $command->run();

        $this->assertDatabaseHas('heartbeats', ['id' => $heartbeat->id]);
    }

    /**
     * @test
     */
    public function it_deletes_the_heartbeat_if_you_confirm()
    {
        $heartbeat = factory(Heartbeat::class)->create();
        $command = $this->artisan('heartbeat:delete', ['name' => $heartbeat->name]);

        $command->expectsQuestion('Would you like to delete this heartbeat?', true);
        $command->expectsOutput("Heartbeat deleted.");

        $command->run();

        $this->assertDatabaseMissing('heartbeats', ['id' => $heartbeat->id]);
    }

    /**
     * @test
     */
    public function it_errors_when_the_heartbeat_doesnt_exist()
    {
        $command = $this->artisan('heartbeat:delete', ['name' => 'wat']);

        $command->expectsOutput("  No heartbeat with the name 'wat' was found.  ");

        $this->assertEquals(1, $command->run());
    }
}
