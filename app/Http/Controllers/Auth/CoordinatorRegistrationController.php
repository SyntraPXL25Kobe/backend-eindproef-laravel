<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreCoordinatorRegistrationRequest;
use App\Services\CoordinatorRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class CoordinatorRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/register-coordinator', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function store(
        StoreCoordinatorRegistrationRequest $request,
        CoordinatorRegistrationService $coordinatorRegistrationService,
    ): RedirectResponse {
        $user = $coordinatorRegistrationService->register($request->validated());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
