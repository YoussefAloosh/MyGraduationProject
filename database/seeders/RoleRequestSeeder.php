<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoleRequest;
use App\Models\User;

class RoleRequestSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $requests = [
            [
                'email'         => 'ahmad@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'pending',
                'submitted_docs'=> 'first_aid_cert.pdf',
            ],
            [
                'email'         => 'mohamad@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'pending',
                'submitted_docs'=> 'rescue_training.pdf',
            ],
            [
                'email'         => 'maysa@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'approved',
                'submitted_docs'=> 'medical_license.pdf',
            ],
            [
                'email'         => 'omar@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'rejected',
                'submitted_docs'=> 'incomplete_docs.pdf',
                'rejection_reason' => 'المستندات المرفقة غير مكتملة، يرجى إرفاق شهادة الإسعافات الأولية.',
            ],
            [
                'email'         => 'bilal@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'pending',
                'submitted_docs'=> 'civil_defense_cert.pdf',
            ],
            [
                'email'         => 'rami@test.com',
                'role_type'     => 'rescuer',
                'status'        => 'pending',
                'submitted_docs'=> 'firefighter_cert.pdf',
            ],
        ];

        $manager = User::where('email', 'emergency@admin.com')->first();

        foreach ($requests as $requestData) {
            $user = User::where('email', $requestData['email'])->first();
            if (!$user) continue;

            RoleRequest::create([
                'user_id'          => $user->id,
                'role_type'        => $requestData['role_type'],
                'status'           => $requestData['status'],
                'submitted_docs'   => $requestData['submitted_docs'],
                'rejection_reason' => $requestData['rejection_reason'] ?? null,
                'reviewed_at'      => in_array($requestData['status'], ['approved', 'rejected']) ? now() : null,
                'reviewed_by'      => in_array($requestData['status'], ['approved', 'rejected']) ? $manager?->id : null,
            ]);
        }

        $this->command->info('✅ Role Requests seeded successfully!');
    }
}