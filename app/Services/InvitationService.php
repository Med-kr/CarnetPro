<?php

namespace App\Services;

use App\Mail\InvitationCreated;
use App\Models\Flatshare;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class InvitationService
{
    public function create(Flatshare $flatshare, string $email): array
    {
        $invitation = Invitation::create([
            'flatshare_id' => $flatshare->id,
            'email' => $email,
            'token' => Str::uuid()->toString(),
            'status' => Invitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
        ]);

        // Load flatshare with owner for email
        $invitation->load('flatshare.owner');

        $mailSent = true;
        $mailError = null;

        try {
            Mail::to($email)->send(new InvitationCreated($invitation));
        } catch (Throwable $throwable) {
            $mailSent = false;
            $mailError = $throwable->getMessage();

            // On journalise l'erreur pour éviter un échec silencieux côté SMTP.
            Log::error('Invitation email failed to send.', [
                'invitation_id' => $invitation->id,
                'flatshare_id' => $flatshare->id,
                'recipient_email' => $email,
                'mailer' => config('mail.default'),
                'error' => $mailError,
            ]);
        }

        return [
            'invitation' => $invitation,
            'mail_sent' => $mailSent,
            'mail_error' => $mailError,
        ];
    }

    public function accept(Invitation $invitation, User $user): Flatshare
    {
        $invitation->loadMissing('flatshare');

        if ($user->email !== $invitation->email) {
            throw ValidationException::withMessages([
                'email' => 'This invitation is not for your email address.',
            ]);
        }

        if ($invitation->isExpired() && $invitation->status === Invitation::STATUS_PENDING) {
            $invitation->update(['status' => Invitation::STATUS_EXPIRED]);
        }

        if (! $invitation->isPending() || $invitation->isExpired()) {
            throw ValidationException::withMessages([
                'email' => 'This invitation is no longer valid.',
            ]);
        }

        if (! $user->is_global_admin && $user->hasActiveFlatshare()) {
            throw ValidationException::withMessages([
                'email' => 'You already belong to an active flatshare.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user) {
            Membership::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'flatshare_id' => $invitation->flatshare_id,
                ],
                [
                    'role' => Membership::ROLE_MEMBER,
                    'joined_at' => now(),
                    'left_at' => null,
                ]
            );

            $invitation->update(['status' => Invitation::STATUS_ACCEPTED]);
        });

        return $invitation->flatshare;
    }
}
