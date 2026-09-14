<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Services\StatusReminderTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function edit(Request $request, StatusReminderTemplateService $templates): View
    {
        return view('email-templates.edit', [
            'template' => $templates->forUser($request->user()),
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, StatusReminderTemplateService $templates): RedirectResponse
    {
        $templates->update(
            $request->user(),
            $request->validated('subject'),
            $request->validated('body'),
        );

        return back()->with('success', 'Email template saved.');
    }
}
