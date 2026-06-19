<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileSkillsUpdateRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Skill;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the user's skills settings page.
     */
    public function editSkills(Request $request): Response
    {
        return Inertia::render('settings/skills', [
            'availableSkills' => Skill::all(),
            'userSkills' => $request->user()->skills()->pluck('skills.id')->values(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profiel bijgewerkt.']);

        return to_route('profile.edit');
    }

    /**
     * Update the user's selected skills.
     */
    public function updateSkills(ProfileSkillsUpdateRequest $request): RedirectResponse
    {
        $request->user()->skills()->sync($request->validated('skills', []));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vaardigheden bijgewerkt.']);

        return to_route('skills.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
