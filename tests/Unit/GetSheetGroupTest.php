<?php

namespace Tests\Unit;

use App\Models\GlsInscription;
use App\Models\Group;
use App\Models\GroupApplication;
use Tests\TestCase;

/**
 * getSheetGroup() must prefer the live Group.time_range over the static
 * config('google-sheets.group_names') map, so newly created groups show a
 * real schedule label in the Google Sheet instead of falling back to
 * "Groupe {id}". The static map stays as a last-resort fallback only.
 *
 * Pure unit tests (no DB): relations are set on unsaved model instances via
 * setRelation() so no database connection is required.
 */
class GetSheetGroupTest extends TestCase
{
    public function test_gls_inscription_uses_group_time_range_when_available(): void
    {
        $group = new Group(['time_range' => '17:00 - 19:00']);

        $inscription = new GlsInscription(['group_id' => 86]);
        $inscription->setRelation('group', $group);

        $this->assertSame('Groupe 17:00 - 19:00', $inscription->getSheetGroup());
    }

    public function test_gls_inscription_falls_back_to_static_map_when_group_missing_time_range(): void
    {
        config(['google-sheets.group_names' => [1 => 'Groupe 10:00 – 12:00']]);

        $inscription = new GlsInscription(['group_id' => 1]);
        $inscription->setRelation('group', null);

        $this->assertSame('Groupe 10:00 – 12:00', $inscription->getSheetGroup());
    }

    public function test_gls_inscription_falls_back_to_raw_id_when_group_and_map_are_both_missing(): void
    {
        config(['google-sheets.group_names' => []]);

        $inscription = new GlsInscription(['group_id' => 999]);
        $inscription->setRelation('group', null);

        $this->assertSame('Groupe 999', $inscription->getSheetGroup());
    }

    public function test_gls_inscription_handles_null_group_id(): void
    {
        $inscription = new GlsInscription(['group_id' => null]);
        $inscription->setRelation('group', null);

        $this->assertSame('Groupe N/A', $inscription->getSheetGroup());
    }

    public function test_group_application_uses_group_time_range_when_available(): void
    {
        $group = new Group(['time_range' => '20:00 - 22:00']);

        $application = new GroupApplication(['group_id' => 88]);
        $application->setRelation('group', $group);

        $this->assertSame('Groupe 20:00 - 22:00', $application->getSheetGroup());
    }

    public function test_group_application_falls_back_to_static_map(): void
    {
        config(['google-sheets.group_names' => [25 => 'Groupe Nuit 20:00 – 22:00']]);

        $application = new GroupApplication(['group_id' => 25]);
        $application->setRelation('group', null);

        $this->assertSame('Groupe Nuit 20:00 – 22:00', $application->getSheetGroup());
    }

    public function test_special_night_group_resolves_via_time_range_not_just_static_map(): void
    {
        // A "Groupe Nuit" style group must resolve correctly purely from
        // time_range too, not only when it happens to be in the static map.
        $group = new Group(['time_range' => 'Nuit 20:00 - 22:00']);

        $inscription = new GlsInscription(['group_id' => 25]);
        $inscription->setRelation('group', $group);

        $this->assertSame('Groupe Nuit 20:00 - 22:00', $inscription->getSheetGroup());
    }

    public function test_legacy_group_1_to_25_still_resolves_via_static_map_if_not_in_db(): void
    {
        // Backward compatibility: old group IDs that only exist in the
        // static config (e.g. already deleted from the groups table) must
        // keep working exactly as before.
        config(['google-sheets.group_names' => [3 => 'Groupe 17:00 – 19:00']]);

        $application = new GroupApplication(['group_id' => 3]);
        $application->setRelation('group', null);

        $this->assertSame('Groupe 17:00 – 19:00', $application->getSheetGroup());
    }
}
