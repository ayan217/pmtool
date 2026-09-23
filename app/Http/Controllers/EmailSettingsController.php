<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmailSettingsRequest;
use App\Services\EmailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailSettingsController extends Controller
{
    public function edit(Request $request, EmailSettingsService $emailSettings): View
    {
        return view('email-settings.edit', [
            'settings' => $emailSettings->forUser($request->user()),
        ]);
    }

    public function update(UpdateEmailSettingsRequest $request, EmailSettingsService $emailSettings): RedirectResponse
    {
        $emailSettings->update(
            $request->user(),
            $request->validated('from_name'),
            $request->validated('admin_email'),
            $request->validated('daily_reminder_time'),
        );

        return back()->with('success', 'Email settings saved.');
    }
}
