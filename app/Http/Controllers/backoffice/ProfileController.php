<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('backoffice.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'bio'           => 'nullable|string',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048'
        ]);

        // Basic fields
        $user->name    = $request->name;
        $user->bio     = $request->bio;
        $user->phone   = $request->phone;
        $user->address = $request->address;

        // Profile photo (Spatie Media Library)
        if ($request->hasFile('profile_photo')) {
            $user->clearMediaCollection('profile_photo');
            $user->addMediaFromRequest('profile_photo')->toMediaCollection('profile_photo');
        }

        $user->save();

        return back()->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }
}
