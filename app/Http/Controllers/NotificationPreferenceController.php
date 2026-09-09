<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationPreferenceController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $categories = NotificationPreference::categoriesForRole($user->role);
        $stored = $user->notificationPreferences()
            ->whereIn('category', array_keys($categories))
            ->get()
            ->keyBy('category');

        $preferences = collect($categories)->mapWithKeys(function (string $label, string $category) use ($stored) {
            $preference = $stored->get($category);

            return [$category => [
                'label' => $label,
                'enabled' => $preference?->enabled ?? true,
                'frequency' => $preference?->frequency ?? NotificationPreference::FREQUENCY_IMMEDIATE,
            ]];
        });

        return view('notification-preferences.edit', [
            'preferences' => $preferences,
            'frequencyOptions' => NotificationPreference::frequencyOptions(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $categories = NotificationPreference::categoriesForRole($user->role);
        $frequencies = array_keys(NotificationPreference::frequencyOptions());

        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.enabled' => ['nullable', 'boolean'],
            'preferences.*.frequency' => ['required', Rule::in($frequencies)],
        ]);

        foreach ($categories as $category => $label) {
            $submitted = $validated['preferences'][$category] ?? [];

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category' => $category,
                ],
                [
                    'enabled' => (bool) ($submitted['enabled'] ?? false),
                    'frequency' => $submitted['frequency'] ?? NotificationPreference::FREQUENCY_IMMEDIATE,
                ]
            );
        }

        return redirect()
            ->route('notification-preferences.edit')
            ->with('status', 'Preferenze notifiche aggiornate.');
    }
}
