<?php

namespace App\Http\Controllers;

use App\Models\OrderNotificationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminOrderNotificationController extends Controller
{
    private const SETTINGS_ID = 1;

    private const MAX_RECIPIENTS = 10;

    public function edit(): View
    {
        $setting = $this->setting();

        return view('admin.order-notifications', [
            'recipientEmails' => $setting->recipient_emails ?? [],
            'deliveryConfigured' => filled(config('services.resend.key'))
                && filled(config('mail.from.address')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
        ], [
            'email.required' => 'Enter an email address to receive booking alerts.',
            'email.email' => 'Enter a valid email address.',
        ]);
        $email = Str::lower(trim($validated['email']));

        DB::transaction(function () use ($email): void {
            $setting = OrderNotificationSetting::query()->lockForUpdate()->findOrFail(self::SETTINGS_ID);
            $emails = collect($setting->recipient_emails ?? [])
                ->map(fn (string $recipient): string => Str::lower(trim($recipient)))
                ->filter()
                ->unique()
                ->values();

            if (! $emails->contains($email) && $emails->count() >= self::MAX_RECIPIENTS) {
                throw ValidationException::withMessages([
                    'email' => 'A maximum of 10 notification recipients is allowed.',
                ]);
            }

            $setting->update([
                'recipient_emails' => $emails->push($email)->unique()->values()->all(),
            ]);
        });

        return redirect()
            ->route('admin.notifications.edit')
            ->with('status', 'Booking alert recipient added.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
        ]);
        $email = Str::lower(trim($validated['email']));

        DB::transaction(function () use ($email): void {
            $setting = OrderNotificationSetting::query()->lockForUpdate()->findOrFail(self::SETTINGS_ID);
            $setting->update([
                'recipient_emails' => collect($setting->recipient_emails ?? [])
                    ->reject(fn (string $recipient): bool => Str::lower(trim($recipient)) === $email)
                    ->values()
                    ->all(),
            ]);
        });

        return redirect()
            ->route('admin.notifications.edit')
            ->with('status', 'Booking alert recipient removed.');
    }

    private function setting(): OrderNotificationSetting
    {
        return OrderNotificationSetting::query()->firstOrCreate(
            ['id' => self::SETTINGS_ID],
            ['recipient_emails' => config('services.resend.order_notifications.emails', [])],
        );
    }
}
