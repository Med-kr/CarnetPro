<?php

namespace Tests\Feature;

use App\Mail\InvitationCreated;
use App\Models\Expense;
use App\Models\Flatshare;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Category;
use App\Models\User;
use App\Services\SettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CarnetProFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_flow_accepts_for_matching_email(): void
    {
        Mail::fake();

        [$owner, $flatshare] = $this->createFlatshareWithOwner();
        $member = User::factory()->create(['email' => 'member@example.com']);

        $this->actingAs($owner)->post(route('flatshares.invitations.store', $flatshare), [
            'email' => $member->email,
        ])->assertRedirect();

        $invitation = Invitation::first();

        Mail::assertSent(InvitationCreated::class, function (InvitationCreated $mail) use ($member, $invitation) {
            return $mail->hasTo($member->email)
                && $mail->acceptUrl === route('invitations.show', $invitation->token);
        });

        $this->actingAs($member)->post(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('flatshares.show', $flatshare));

        $this->assertDatabaseHas('memberships', [
            'user_id' => $member->id,
            'flatshare_id' => $flatshare->id,
            'role' => Membership::ROLE_MEMBER,
            'left_at' => null,
        ]);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => Invitation::STATUS_ACCEPTED,
        ]);
    }

    public function test_guest_invitation_redirects_existing_user_to_login(): void
    {
        [, $flatshare] = $this->createFlatshareWithOwner();
        $member = User::factory()->create(['email' => 'member@example.com']);

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => $member->email,
            'token' => 'existing-user-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('invitations.show', $invitation->token))
            ->assertRedirect(route('login', ['invitation' => $invitation->token]));
    }

    public function test_guest_invitation_redirects_new_user_to_register(): void
    {
        [, $flatshare] = $this->createFlatshareWithOwner();

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => 'new-member@example.com',
            'token' => 'new-user-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->get(route('invitations.show', $invitation->token))
            ->assertRedirect(route('register', ['invitation' => $invitation->token]));
    }

    public function test_login_with_invitation_accepts_and_redirects_to_flatshare(): void
    {
        [, $flatshare] = $this->createFlatshareWithOwner();
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password',
        ]);

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => $member->email,
            'token' => 'login-invite-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->post(route('login'), [
            'email' => $member->email,
            'password' => 'password',
            'invitation' => $invitation->token,
        ])->assertRedirect(route('flatshares.show', $flatshare));

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => Invitation::STATUS_ACCEPTED,
        ]);
    }

    public function test_register_with_invitation_accepts_and_redirects_to_flatshare(): void
    {
        [, $flatshare] = $this->createFlatshareWithOwner();

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => 'new-member@example.com',
            'token' => 'register-invite-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->post(route('register'), [
            'name' => 'New Member',
            'email' => $invitation->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'invitation' => $invitation->token,
        ])->assertRedirect(route('flatshares.show', $flatshare));

        $this->assertDatabaseHas('users', [
            'email' => $invitation->email,
        ]);

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => Invitation::STATUS_ACCEPTED,
        ]);
    }

    public function test_first_registered_user_becomes_global_admin(): void
    {
        $this->post(route('register'), [
            'name' => 'Admin Seed',
            'email' => 'admin-seed@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin-seed@example.com',
            'is_global_admin' => true,
        ]);
    }

    public function test_invitation_is_created_even_if_email_delivery_fails(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();
        Log::spy();

        Mail::shouldReceive('to->send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP unavailable'));

        $response = $this->actingAs($owner)->post(route('flatshares.invitations.store', $flatshare), [
            'email' => 'member@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');
        $response->assertSessionHas('invitation_url');

        $this->assertDatabaseHas('invitations', [
            'flatshare_id' => $flatshare->id,
            'email' => 'member@example.com',
            'status' => Invitation::STATUS_PENDING,
        ]);

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === 'Invitation email failed to send.'
                    && $context['recipient_email'] === 'member@example.com'
                    && $context['error'] === 'SMTP unavailable';
            });
    }

    public function test_owner_can_remove_an_invitation(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => 'member@example.com',
            'token' => 'remove-invite-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($owner)
            ->delete(route('flatshares.invitations.destroy', [$flatshare, $invitation]))
            ->assertRedirect();

        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_invitation_accept_is_rejected_for_different_email(): void
    {
        [, $flatshare] = $this->createFlatshareWithOwner();
        $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);

        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => 'expected@example.com',
            'token' => 'email-mismatch-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($wrongUser)
            ->post(route('invitations.accept', $invitation->token))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'status' => Invitation::STATUS_PENDING,
        ]);

        $this->assertDatabaseMissing('memberships', [
            'user_id' => $wrongUser->id,
            'flatshare_id' => $flatshare->id,
        ]);
    }

    public function test_owner_can_create_category_with_icon(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();

        $this->actingAs($owner)->post(route('flatshares.categories.store', $flatshare), [
            'name' => 'Groceries',
            'icon' => 'groceries',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'flatshare_id' => $flatshare->id,
            'name' => 'Groceries',
            'icon' => 'groceries',
        ]);
    }

    public function test_owner_can_create_category_without_submitting_icon(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();

        $this->actingAs($owner)->post(route('flatshares.categories.store', $flatshare), [
            'name' => 'Internet',
            'icon' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'flatshare_id' => $flatshare->id,
            'name' => 'Internet',
            'icon' => 'tag',
        ]);
    }

    public function test_owner_can_update_category_icon_without_resubmitting_name(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();

        $category = Category::create([
            'flatshare_id' => $flatshare->id,
            'name' => 'Utilities',
            'icon' => 'utilities',
        ]);

        $this->actingAs($owner)->put(route('flatshares.categories.update', [$flatshare, $category]), [
            'name' => '',
            'icon' => 'internet',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Utilities',
            'icon' => 'internet',
        ]);
    }

    public function test_owner_can_update_category_name_without_resubmitting_icon(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();

        $category = Category::create([
            'flatshare_id' => $flatshare->id,
            'name' => 'Food',
            'icon' => 'food',
        ]);

        $this->actingAs($owner)->put(route('flatshares.categories.update', [$flatshare, $category]), [
            'name' => 'Dining',
            'icon' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Dining',
            'icon' => 'food',
        ]);
    }

    public function test_user_cannot_create_new_flatshare_when_already_in_active_flatshare(): void
    {
        [$user] = $this->createFlatshareWithOwner();
        [, $otherFlatshare] = $this->createFlatshareWithOwner();

        $this->actingAs($user)->get(route('flatshares.create'))->assertForbidden();

        $this->actingAs($user)->post(route('flatshares.store'), [
            'name' => 'Second Draft Flatshare',
        ])->assertForbidden();

        $this->assertDatabaseMissing('flatshares', [
            'name' => 'Second Draft Flatshare',
            'owner_id' => $user->id,
        ]);

        Invitation::create([
            'flatshare_id' => $otherFlatshare->id,
            'email' => $user->email,
            'token' => 'invite-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->post(route('invitations.accept', 'invite-token'))
            ->assertSessionHasErrors('email');
    }

    public function test_global_admin_can_have_multiple_active_flatshares(): void
    {
        $admin = User::factory()->create(['is_global_admin' => true]);

        $this->actingAs($admin)->post(route('flatshares.store'), [
            'name' => 'Admin Flatshare A',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('flatshares.store'), [
            'name' => 'Admin Flatshare B',
        ])->assertRedirect();

        $this->assertDatabaseCount('flatshares', 2);
        $this->assertDatabaseCount('memberships', 2);
    }

    public function test_flatshare_creation_seeds_default_categories(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('flatshares.store'), [
            'name' => 'Seeded Flatshare',
        ])->assertRedirect();

        $flatshare = Flatshare::where('name', 'Seeded Flatshare')->firstOrFail();

        foreach (\App\Models\Category::defaultDefinitions() as $definition) {
            $this->assertDatabaseHas('categories', [
                'flatshare_id' => $flatshare->id,
                'name' => $definition['name'],
                'icon' => $definition['icon'],
            ]);
        }
    }

    public function test_flatshare_is_created_deactivated_by_default(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->post(route('flatshares.store'), [
            'name' => 'Draft Flatshare',
        ])->assertRedirect();

        $this->assertDatabaseHas('flatshares', [
            'name' => 'Draft Flatshare',
            'owner_id' => $owner->id,
            'status' => Flatshare::STATUS_CANCELLED,
        ]);
    }

    public function test_user_can_create_new_flatshare_when_existing_ones_are_deactivated(): void
    {
        $owner = User::factory()->create();

        $firstFlatshare = Flatshare::create([
            'name' => 'Old Disabled Flatshare',
            'owner_id' => $owner->id,
            'status' => Flatshare::STATUS_CANCELLED,
        ]);

        Membership::create([
            'user_id' => $owner->id,
            'flatshare_id' => $firstFlatshare->id,
            'role' => Membership::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('flatshares.store'), [
            'name' => 'New Disabled Flatshare',
        ])->assertRedirect();

        $this->assertDatabaseHas('flatshares', [
            'name' => 'New Disabled Flatshare',
            'owner_id' => $owner->id,
            'status' => Flatshare::STATUS_CANCELLED,
        ]);
    }

    public function test_global_admin_can_accept_invitation_even_with_active_flatshare(): void
    {
        $admin = User::factory()->create(['is_global_admin' => true]);
        $ownedFlatshare = Flatshare::create([
            'name' => 'Admin Base',
            'owner_id' => $admin->id,
            'status' => Flatshare::STATUS_ACTIVE,
        ]);

        Membership::create([
            'user_id' => $admin->id,
            'flatshare_id' => $ownedFlatshare->id,
            'role' => Membership::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        [, $otherFlatshare] = $this->createFlatshareWithOwner();

        $invitation = Invitation::create([
            'flatshare_id' => $otherFlatshare->id,
            'email' => $admin->email,
            'token' => 'admin-invite-token',
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)->post(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('flatshares.show', $otherFlatshare));

        $this->assertDatabaseHas('memberships', [
            'user_id' => $admin->id,
            'flatshare_id' => $otherFlatshare->id,
            'role' => Membership::ROLE_MEMBER,
            'left_at' => null,
        ]);
    }

    public function test_former_member_cannot_view_flatshare_after_leaving(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        $flatshare->memberships()
            ->where('user_id', $member->id)
            ->update(['left_at' => now()]);

        $this->actingAs($member)
            ->get(route('flatshares.show', $flatshare))
            ->assertRedirect(route('flatshares.index'));

        $this->actingAs($member)
            ->get(route('flatshares.index'))
            ->assertSee('Past flatshares')
            ->assertSee($flatshare->name)
            ->assertSee('Left');
    }

    public function test_former_member_is_redirected_from_expenses_and_settlements_links(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        $flatshare->memberships()
            ->where('user_id', $member->id)
            ->update(['left_at' => now()]);

        $this->actingAs($member)
            ->get(route('flatshares.expenses.index', $flatshare))
            ->assertRedirect(route('flatshares.index'));

        $this->actingAs($member)
            ->get(route('flatshares.show', ['flatshare' => $flatshare, 'tab' => 'settlements']))
            ->assertRedirect(route('flatshares.index'));
    }

    public function test_member_leave_with_debt_redistributes_balance_and_updates_reputation(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $this->actingAs($memberTwo)
            ->post(route('flatshares.leave', $flatshare))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('memberships', [
            'user_id' => $memberTwo->id,
            'flatshare_id' => $flatshare->id,
        ]);

        $this->assertDatabaseHas('adjustments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 25.00,
        ]);

        $this->assertDatabaseHas('adjustments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $memberOne->id,
            'amount' => 25.00,
        ]);

        $this->assertSame(-1, $memberTwo->fresh()->reputation);

        $balances = app(SettlementService::class)->calculateBalances($flatshare->fresh())->keyBy('user.id');

        $this->assertSame(15.0, (float) $balances[$owner->id]['balance']);
        $this->assertSame(-15.0, (float) $balances[$memberOne->id]['balance']);
    }

    public function test_member_leave_without_debt_increases_reputation(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Rent',
            'amount' => 100,
            'spent_at' => now()->toDateString(),
        ]);

        Payment::create([
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $member->id,
            'to_user_id' => $owner->id,
            'amount' => 50,
            'settlement_amount' => 50,
            'applied_amount' => 50,
            'credit_amount' => 0,
            'method' => Payment::METHOD_CASH,
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        $this->actingAs($member)
            ->post(route('flatshares.leave', $flatshare))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, $member->fresh()->reputation);
    }

    public function test_owner_removal_with_debt_imputes_member_debt_to_owner(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Rent',
            'amount' => 100,
            'spent_at' => now()->toDateString(),
        ]);

        $membership = $flatshare->memberships()
            ->where('user_id', $member->id)
            ->whereNull('left_at')
            ->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('flatshares.memberships.destroy', [$flatshare, $membership]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('adjustments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $member->id,
            'to_user_id' => $owner->id,
            'amount' => 50.00,
            'reason' => 'Owner took over member debt on removal.',
        ]);

        $this->assertSame(-1, $member->fresh()->reputation);

        $balances = app(SettlementService::class)->calculateBalances($flatshare->fresh())->keyBy('user.id');

        $this->assertSame(0.0, (float) $balances[$owner->id]['balance']);
    }

    public function test_flatshare_cancellation_updates_reputation_based_on_debt(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Rent',
            'amount' => 100,
            'spent_at' => now()->toDateString(),
        ]);

        $this->actingAs($owner)
            ->post(route('flatshares.cancel', $flatshare))
            ->assertSessionHas('success');

        $this->assertSame(1, $owner->fresh()->reputation);
        $this->assertSame(-1, $member->fresh()->reputation);
    }

    public function test_flatshare_cancellation_without_debt_increases_reputation_for_active_members(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Rent',
            'amount' => 100,
            'spent_at' => now()->toDateString(),
        ]);

        Payment::create([
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $member->id,
            'to_user_id' => $owner->id,
            'amount' => 50,
            'settlement_amount' => 50,
            'applied_amount' => 50,
            'credit_amount' => 0,
            'method' => Payment::METHOD_CASH,
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('flatshares.cancel', $flatshare))
            ->assertSessionHas('success');

        $this->assertSame(1, $owner->fresh()->reputation);
        $this->assertSame(1, $member->fresh()->reputation);
    }

    public function test_settlements_page_displays_expected_transfers(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($owner)->get(route('flatshares.settlements.show', $flatshare));

        $response->assertOk();
        $response->assertSee($memberTwo->name.' owes '.$owner->name);
        $response->assertSee($memberTwo->name.' owes '.$memberOne->name);
    }

    public function test_banned_user_is_logged_out_by_middleware(): void
    {
        $user = User::factory()->create(['is_banned' => true]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_member_cannot_invite_or_cancel_flatshare(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        $this->actingAs($member)->post(route('flatshares.invitations.store', $flatshare), [
            'email' => 'new@example.com',
        ])->assertForbidden();

        $this->actingAs($member)->post(route('flatshares.cancel', $flatshare))
            ->assertForbidden();
    }

    public function test_owner_cannot_invite_into_deactivated_flatshare(): void
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();
        $flatshare->update(['status' => Flatshare::STATUS_CANCELLED]);

        $this->actingAs($owner)->post(route('flatshares.invitations.store', $flatshare), [
            'email' => 'blocked@example.com',
        ])->assertForbidden();
    }

    public function test_owner_cannot_be_removed_from_flatshare(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        $ownerMembership = $flatshare->memberships()
            ->where('user_id', $owner->id)
            ->where('role', Membership::ROLE_OWNER)
            ->firstOrFail();

        $this->actingAs($owner)
            ->delete(route('flatshares.memberships.destroy', [$flatshare, $ownerMembership]))
            ->assertStatus(422);

        $this->assertDatabaseHas('memberships', [
            'id' => $ownerMembership->id,
            'left_at' => null,
        ]);
    }

    public function test_reactivation_is_blocked_when_member_already_has_another_active_flatshare(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();
        $flatshare->update(['status' => Flatshare::STATUS_CANCELLED]);

        $otherFlatshare = Flatshare::create([
            'name' => 'Other Active Flatshare',
            'owner_id' => $member->id,
            'status' => Flatshare::STATUS_ACTIVE,
        ]);

        Membership::create([
            'user_id' => $member->id,
            'flatshare_id' => $otherFlatshare->id,
            'role' => Membership::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('flatshares.cancel', $flatshare))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('flatshares', [
            'id' => $flatshare->id,
            'status' => Flatshare::STATUS_CANCELLED,
        ]);
    }

    public function test_mark_paid_creates_payment_and_updates_settlements(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $service = app(SettlementService::class);
        $settlements = $service->calculateSettlements($flatshare);
        $targetSettlement = $settlements->firstWhere('to_user.id', $owner->id);

        $this->actingAs($memberTwo)->post(route('flatshares.payments.store', $flatshare), [
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => $targetSettlement['amount'],
            'method_option' => 'cash',
            'status' => 'completed',
            'reference' => 'PAY-001',
            'note' => 'Paid in cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'method' => 'cash',
            'status' => 'completed',
            'reference' => 'PAY-001',
            'note' => 'Paid in cash',
        ]);

        $updatedSettlements = $service->calculateSettlements($flatshare->fresh());

        $this->assertFalse(
            $updatedSettlements->contains(fn (array $settlement) => $settlement['to_user']->id === $owner->id)
        );
    }

    public function test_mark_paid_redirects_back_to_current_settlements_screen(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $service = app(SettlementService::class);
        $settlement = $service->calculateSettlements($flatshare)->firstWhere('to_user.id', $owner->id);
        $redirectTo = route('flatshares.show', ['flatshare' => $flatshare, 'tab' => 'settlements']);

        $this->actingAs($memberTwo)->post(route('flatshares.payments.store', $flatshare), [
            'redirect_to' => $redirectTo,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => $settlement['amount'],
            'method_option' => 'bank_transfer',
            'status' => 'completed',
        ])->assertRedirect($redirectTo);
    }

    public function test_overpayment_is_kept_as_account_credit(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $service = app(SettlementService::class);
        $settlement = $service->calculateSettlements($flatshare)
            ->firstWhere('to_user.id', $owner->id);

        $this->actingAs($memberTwo)->post(route('flatshares.payments.store', $flatshare), [
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 100,
            'method_option' => 'card',
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 100,
            'method' => 'card',
            'status' => 'completed',
            'settlement_amount' => (float) $settlement['amount'],
            'applied_amount' => (float) $settlement['amount'],
            'credit_amount' => round(100 - (float) $settlement['amount'], 2),
        ]);

        $balances = $service->calculateBalances($flatshare->fresh())->keyBy('user.id');

        $this->assertSame(50.0, (float) $balances[$memberTwo->id]['balance']);
    }

    public function test_partial_payment_keeps_remaining_amount_in_settlements(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $service = app(SettlementService::class);
        $settlement = $service->calculateSettlements($flatshare)
            ->firstWhere('to_user.id', $owner->id);

        $this->actingAs($memberTwo)->post(route('flatshares.payments.store', $flatshare), [
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 10,
            'method_option' => 'mobile_wallet',
            'status' => 'completed',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 10,
            'method' => 'mobile_wallet',
            'status' => 'completed',
        ]);

        $updatedSettlement = $service->calculateSettlements($flatshare->fresh())
            ->firstWhere('to_user.id', $owner->id);

        $this->assertSame(round($settlement['amount'] - 10, 2), (float) $updatedSettlement['amount']);
    }

    public function test_pending_payment_does_not_reduce_remaining_settlement(): void
    {
        [$owner, $flatshare, $memberOne, $memberTwo] = $this->createFlatshareWithThreeMembers();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'Groceries',
            'amount' => 90,
            'spent_at' => now()->toDateString(),
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $memberOne->id,
            'title' => 'Utilities',
            'amount' => 60,
            'spent_at' => now()->toDateString(),
        ]);

        $service = app(SettlementService::class);
        $settlement = $service->calculateSettlements($flatshare)
            ->firstWhere('to_user.id', $owner->id);

        $this->actingAs($memberTwo)->post(route('flatshares.payments.store', $flatshare), [
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 10,
            'method_option' => 'custom',
            'custom_method' => 'PayPal',
            'status' => 'pending',
            'reference' => 'PP-REF',
            'note' => 'Waiting confirmation',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'flatshare_id' => $flatshare->id,
            'from_user_id' => $memberTwo->id,
            'to_user_id' => $owner->id,
            'amount' => 10,
            'method' => 'PayPal',
            'status' => 'pending',
            'reference' => 'PP-REF',
            'note' => 'Waiting confirmation',
        ]);

        $updatedSettlement = $service->calculateSettlements($flatshare->fresh())
            ->firstWhere('to_user.id', $owner->id);

        $this->assertSame((float) $settlement['amount'], (float) $updatedSettlement['amount']);
    }

    public function test_month_filter_applies_to_expense_stats(): void
    {
        [$owner, $flatshare, $member] = $this->createFlatshareWithOwnerAndMember();

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $owner->id,
            'title' => 'March Groceries',
            'amount' => 50,
            'spent_at' => '2026-03-10',
        ]);

        Expense::create([
            'flatshare_id' => $flatshare->id,
            'payer_id' => $member->id,
            'title' => 'April Bills',
            'amount' => 120,
            'spent_at' => '2026-04-02',
        ]);

        $overview = app(SettlementService::class)->buildOverview($flatshare, '2026-03');

        $this->assertSame(50.0, $overview['expenseStats']['total_expenses']);
        $this->assertSame(1, $overview['expenseStats']['expense_count']);
        $this->assertCount(1, $overview['expenseStats']['category_totals']);
    }

    protected function createFlatshareWithOwner(): array
    {
        $owner = User::factory()->create();
        $flatshare = Flatshare::create([
            'name' => 'Main Flatshare',
            'owner_id' => $owner->id,
            'status' => Flatshare::STATUS_ACTIVE,
        ]);

        Membership::create([
            'user_id' => $owner->id,
            'flatshare_id' => $flatshare->id,
            'role' => Membership::ROLE_OWNER,
            'joined_at' => now(),
        ]);

        return [$owner, $flatshare];
    }

    protected function createFlatshareWithOwnerAndMember(): array
    {
        [$owner, $flatshare] = $this->createFlatshareWithOwner();
        $member = User::factory()->create();

        Membership::create([
            'user_id' => $member->id,
            'flatshare_id' => $flatshare->id,
            'role' => Membership::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        return [$owner, $flatshare, $member];
    }

    protected function createFlatshareWithThreeMembers(): array
    {
        [$owner, $flatshare, $memberOne] = $this->createFlatshareWithOwnerAndMember();
        $memberTwo = User::factory()->create();

        Membership::create([
            'user_id' => $memberTwo->id,
            'flatshare_id' => $flatshare->id,
            'role' => Membership::ROLE_MEMBER,
            'joined_at' => now(),
        ]);

        return [$owner, $flatshare, $memberOne, $memberTwo];
    }
}
