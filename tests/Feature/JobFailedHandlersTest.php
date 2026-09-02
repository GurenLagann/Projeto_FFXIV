<?php

namespace Tests\Feature;

use App\Jobs\SyncAlertItemsJob;
use App\Jobs\SyncRecipePageJob;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class JobFailedHandlersTest extends TestCase
{
    public function test_sync_alert_items_job_logs_failure(): void
    {
        Log::spy();

        (new SyncAlertItemsJob())->failed(new \RuntimeException('boom'));

        Log::shouldHaveReceived('error')
            ->with('SyncAlertItemsJob failed', ['error' => 'boom'])
            ->once();
    }

    public function test_sync_recipe_page_job_logs_failure_with_page_context(): void
    {
        Log::spy();

        (new SyncRecipePageJob(page: 3, limit: 100))->failed(new \RuntimeException('boom'));

        Log::shouldHaveReceived('error')
            ->with('SyncRecipePageJob failed', ['page' => 3, 'limit' => 100, 'error' => 'boom'])
            ->once();
    }
}
