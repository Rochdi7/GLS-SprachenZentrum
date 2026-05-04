<?php

namespace Tests\Feature;

use App\Jobs\SyncConfirmedLeadToGoogleSheetJob;
use App\Jobs\SyncLeadToGoogleSheetJob;
use App\Models\GlsInscription;
use App\Models\Group;
use App\Models\GroupApplication;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GroupApplicationSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_creating_group_application_dispatches_sync_job(): void
    {
        $site = Site::factory()->create(['slug' => 'agadir', 'name' => 'Agadir']);
        $group = Group::factory()->create(['site_id' => $site->id]);

        $application = GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => 'Test User',
            'whatsapp_number' => '+212600000000',
            'email' => 'test@example.com',
            'status' => 'pending',
        ]);

        Queue::assertPushed(SyncLeadToGoogleSheetJob::class, function ($job) use ($application) {
            return $job->lead->id === $application->id && $job->lead instanceof GroupApplication;
        });
    }

    public function test_creating_gls_inscription_dispatches_sync_job(): void
    {
        $site = Site::factory()->create(['slug' => 'casablanca', 'name' => 'Casablanca']);

        $inscription = GlsInscription::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+212600000000',
            'adresse' => '123 Rue Test',
            'niveau' => 'A1',
            'centre' => $site->id,
        ]);

        Queue::assertPushed(SyncLeadToGoogleSheetJob::class, function ($job) use ($inscription) {
            return $job->lead->id === $inscription->id && $job->lead instanceof GlsInscription;
        });
    }

    public function test_approving_application_dispatches_confirmed_sync_job(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $group = Group::factory()->create(['site_id' => $site->id]);

        $application = GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => 'Test User',
            'whatsapp_number' => '+212600000001',
            'email' => 'test2@example.com',
            'status' => 'pending',
        ]);

        Queue::fake();

        $application->setStatus('approved')->save();

        Queue::assertPushed(SyncConfirmedLeadToGoogleSheetJob::class, function ($job) use ($application) {
            return $job->lead->id === $application->id;
        });
    }

    public function test_rejecting_application_does_not_dispatch_confirmed_job(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $group = Group::factory()->create(['site_id' => $site->id]);

        $application = GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => 'Test User',
            'whatsapp_number' => '+212600000002',
            'email' => 'test3@example.com',
            'status' => 'pending',
        ]);

        Queue::fake();
        $application->setStatus('rejected')->save();

        Queue::assertNotPushed(SyncConfirmedLeadToGoogleSheetJob::class);
    }

    public function test_updating_non_status_field_does_not_dispatch_confirmed_job(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $group = Group::factory()->create(['site_id' => $site->id]);

        $application = GroupApplication::create([
            'group_id' => $group->id,
            'full_name' => 'Test User',
            'whatsapp_number' => '+212600000003',
            'email' => 'test4@example.com',
            'status' => 'pending',
        ]);

        Queue::fake();
        $application->update(['note' => 'Updated note']);

        Queue::assertNotPushed(SyncConfirmedLeadToGoogleSheetJob::class);
    }
}
