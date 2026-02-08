<?php

use App\Jobs\KaifyTask;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('can dispatch KaifyTask successfully', function () {
    // Skip if no servers available
    $server = Server::where('ip', '!=', '1.2.3.4')->first();

    if (! $server) {
        $this->markTestSkipped('No servers available for testing');
    }

    Queue::fake();

    // Create an activity for the task
    $activity = activity()
        ->withProperties([
            'server_uuid' => $server->uuid,
            'command' => 'echo "test"',
            'type' => 'inline',
        ])
        ->event('inline')
        ->log('[]');

    // Dispatch the job
    KaifyTask::dispatch(
        activity: $activity,
        ignore_errors: false,
        call_event_on_finish: null,
        call_event_data: null
    );

    // Assert job was dispatched
    Queue::assertPushed(KaifyTask::class);
});

it('has correct retry configuration on KaifyTask', function () {
    $server = Server::where('ip', '!=', '1.2.3.4')->first();

    if (! $server) {
        $this->markTestSkipped('No servers available for testing');
    }

    $activity = activity()
        ->withProperties([
            'server_uuid' => $server->uuid,
            'command' => 'echo "test"',
            'type' => 'inline',
        ])
        ->event('inline')
        ->log('[]');

    $job = new KaifyTask(
        activity: $activity,
        ignore_errors: false,
        call_event_on_finish: null,
        call_event_data: null
    );

    // Assert retry configuration
    expect($job->tries)->toBe(3);
    expect($job->maxExceptions)->toBe(1);
    expect($job->timeout)->toBe(600);
    expect($job->backoff())->toBe([30, 90, 180]);
});
