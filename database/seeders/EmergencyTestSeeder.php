<?php

namespace Database\Seeders;

use App\Models\Emergency;
use App\Models\EmergencyGroup;
use App\Models\EmergencyNotification;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds ready-to-test data for the Emergency section:
 *  - Sets is_ratable = true for all trusted-role users
 *  - Ensures trusted@test.com is an active member of the Aleppo group
 *  - Creates a fresh "new" emergency in Aleppo with a pending notification for trusted@test.com
 */
class EmergencyTestSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Enable ratings for all trusted users ──────────────────────────
        User::role('trusted')->update(['is_ratable' => true]);
        User::role('rescuer')->update(['is_ratable' => true]);
        $this->command->info('✅ is_ratable enabled for trusted & rescuer users');

        // ── 2. Ensure trusted@test.com is in the Aleppo group ────────────────
        $trusted = User::where('email', 'trusted@test.com')->first();
        $aleppoGroup = EmergencyGroup::where('name', 'غروب حلب - العزيزية')->first();

        if ($trusted && $aleppoGroup) {
            GroupMember::updateOrCreate(
                ['user_id' => $trusted->id, 'group_id' => $aleppoGroup->id],
                [
                    'membership_type'   => 'permanent',
                    'membership_status' => 'active',
                    'is_active'         => true,
                    'joined_at'         => now(),
                    'last_activity_at'  => now(),
                ]
            );
            $this->command->info("✅ trusted@test.com joined [{$aleppoGroup->name}] (id: {$aleppoGroup->id})");
        }

        // ── 3. Ensure lina@test.com is in the Damascus-Kafarsouseh group ─────
        $lina = User::where('email', 'lina@test.com')->first();
        $damascusGroup = EmergencyGroup::where('name', 'غروب دمشق - كفرسوسة')->first();

        if ($lina && $damascusGroup) {
            GroupMember::updateOrCreate(
                ['user_id' => $lina->id, 'group_id' => $damascusGroup->id],
                [
                    'membership_type'   => 'permanent',
                    'membership_status' => 'active',
                    'is_active'         => true,
                    'joined_at'         => now(),
                    'last_activity_at'  => now(),
                ]
            );
        }

        // ── 4. Fresh emergency + pending notification for trusted@test.com ───
        if ($trusted && $aleppoGroup) {
            // Reporter = a different member in the group (not trusted itself)
            $reporter = User::where('email', 'member@test.com')->first() ?? User::first();

            // Ensure reporter is also in the group
            GroupMember::updateOrCreate(
                ['user_id' => $reporter->id, 'group_id' => $aleppoGroup->id],
                [
                    'membership_type'   => 'permanent',
                    'membership_status' => 'active',
                    'is_active'         => true,
                    'joined_at'         => now(),
                ]
            );

            $emergency = Emergency::create([
                'reporter_id'       => $reporter->id,
                'target_group_id'   => $aleppoGroup->id,
                'case_type'         => 'حريق',
                'custom_text'       => 'اختبار: حريق في مبنى سكني',
                'severity'          => 'high',
                'required_rescuers' => Emergency::requiredRescuers('high'),
                'location_lat'      => 36.2021,
                'location_lng'      => 37.1343,
                'status'            => 'new',
                'retry_count'       => 0,
            ]);

            // Pending notification for trusted@test.com
            EmergencyNotification::create([
                'emergency_id' => $emergency->id,
                'receiver_id'  => $trusted->id,
                'is_read'      => false,
                'is_responded' => false,
                'notif_round'  => 1,
                'sent_at'      => now(),
            ]);

            $this->command->info("✅ Fresh emergency #{$emergency->id} created → pending notification for trusted@test.com");
            $this->command->info("   Emergency ID: {$emergency->id}");
            $this->command->info("   Group ID: {$aleppoGroup->id}");
        }

        // ── 5. Print test summary ─────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('─────────────────────────────────────────────────');
        $this->command->info('Test accounts for Emergency section:');
        $this->command->info('  trusted@test.com  / password  (role: trusted)');
        $this->command->info('  lina@test.com     / password  (role: trusted)');
        $this->command->info('  member@test.com   / password  (role: member)');
        $this->command->info('─────────────────────────────────────────────────');
    }
}
