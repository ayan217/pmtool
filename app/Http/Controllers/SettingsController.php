<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request, SettingsService $settings): View
    {
        return view('settings.index', [
            'user' => $request->user(),
            'settings' => $settings->all($request->user()),
        ]);
    }

    public function update(UpdateSettingsRequest $request, SettingsService $settings): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->only('name', 'email'));

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        $settings->update($user, $request->only([
            'notify_dev_deadlines',
            'notify_client_deadlines',
            'reminder_hours_dev',
            'reminder_hours_client',
        ]));

        return back()->with('success', 'Settings updated.');
    }
}
