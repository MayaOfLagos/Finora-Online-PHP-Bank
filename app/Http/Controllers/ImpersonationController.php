<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function stop(): RedirectResponse
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $impersonatorId = session('impersonator_id');
        $impersonatedUser = Auth::user();

        // Clear impersonation session
        session()->forget('impersonator_id');

        // Login back as admin
        $admin = User::find($impersonatorId);
        Auth::login($admin);

        return redirect()
            ->route('filament.admin.resources.users.view', ['record' => $impersonatedUser->uuid])
            ->with('success', 'Impersonation stopped. You are now logged in as admin.');
    }
}
