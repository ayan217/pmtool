<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $user->save();

        $settings->update($user, $request->only([
            'notify_dev_deadlines',
            'notify_client_deadlines',
            'reminder_hours_dev',
            'reminder_hours_client',
        ]));

        return back()->with('success', 'Settings updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        Auth::logoutOtherDevices($request->validated('password'));
        $request->session()->regenerate();

        return back()->with('success', 'Password updated.');
    }
}
