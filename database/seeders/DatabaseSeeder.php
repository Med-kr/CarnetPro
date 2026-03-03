<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Flatshare;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::firstOrCreate(
                ['email' => 'admin@carnetpro.test'],
                [
                    'name' => 'Global Admin',
                    'password' => 'password',
                    'is_global_admin' => true,
                    'email_verified_at' => now(),
                ]
            );

            $owner = User::firstOrCreate(
                ['email' => 'owner@carnetpro.test'],
                [
                    'name' => 'Flatshare Owner',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $memberOne = User::firstOrCreate(
                ['email' => 'member1@carnetpro.test'],
                [
                    'name' => 'Alice Member',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $memberTwo = User::firstOrCreate(
                ['email' => 'member2@carnetpro.test'],
                [
                    'name' => 'Bob Member',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $flatshare = Flatshare::firstOrCreate(
                ['name' => 'CarnetPro Demo House'],
                [
                    'owner_id' => $owner->id,
                    'status' => Flatshare::STATUS_ACTIVE,
                ]
            );

            foreach ([
                [$owner, Membership::ROLE_OWNER],
                [$memberOne, Membership::ROLE_MEMBER],
                [$memberTwo, Membership::ROLE_MEMBER],
            ] as [$user, $role]) {
                Membership::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'flatshare_id' => $flatshare->id,
                    ],
                    [
                        'role' => $role,
                        'joined_at' => now()->subDays(10),
                        'left_at' => null,
                    ]
                );
            }

            $groceries = Category::firstOrCreate([
                'flatshare_id' => $flatshare->id,
                'name' => 'Groceries',
            ]);

            $utilities = Category::firstOrCreate([
                'flatshare_id' => $flatshare->id,
                'name' => 'Utilities',
            ]);

            Expense::firstOrCreate([
                'flatshare_id' => $flatshare->id,
                'title' => 'Weekly groceries',
                'amount' => 90,
                'spent_at' => now()->subDays(6)->toDateString(),
            ], [
                'category_id' => $groceries->id,
                'payer_id' => $owner->id,
            ]);

            Expense::firstOrCreate([
                'flatshare_id' => $flatshare->id,
                'title' => 'Electricity bill',
                'amount' => 60,
                'spent_at' => now()->subDays(3)->toDateString(),
            ], [
                'category_id' => $utilities->id,
                'payer_id' => $memberOne->id,
            ]);

            Payment::firstOrCreate([
                'flatshare_id' => $flatshare->id,
                'from_user_id' => $memberTwo->id,
                'to_user_id' => $owner->id,
                'amount' => 20,
            ], [
                'paid_at' => now()->subDay(),
            ]);

            User::firstOrCreate(
                ['email' => 'test@example.com'],
                [
                    'name' => 'Test User',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $admin->update(['is_global_admin' => true]);
        });
    }
}
