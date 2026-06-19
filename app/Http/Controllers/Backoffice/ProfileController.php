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
            'profile_photo' => 'nullable|image|max:2048',
            'preset_avatar' => 'nullable|integer|between:1,13',
        ]);

        // Basic fields
        $user->name    = $request->name;
        $user->bio     = $request->bio;
        $user->phone   = $request->phone;
        $user->address = $request->address;

        // Profile photo — uploaded file takes priority over preset
        if ($request->hasFile('profile_photo')) {
            $user->clearMediaCollection('profile_photo');
            $user->addMediaFromRequest('profile_photo')->toMediaCollection('profile_photo');
        } elseif ($request->filled('preset_avatar')) {
            $path = public_path('build/images/user/avatar-' . $request->preset_avatar . '.jpg');
            if (file_exists($path)) {
                $user->clearMediaCollection('profile_photo');
                $user->addMedia($path)->preservingOriginal()->toMediaCollection('profile_photo');
            }
        }

        $user->save();

        return back()->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:6',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'        => 'La confirmation du mot de passe ne correspond pas.',
            'password.min'              => 'Le mot de passe doit contenir au moins :min caractères.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ])->with('active_tab', 'password');
        }

        $user->password = $request->password;
        $user->save();

        return back()->with('success', 'Votre mot de passe a été mis à jour avec succès.')
                     ->with('active_tab', 'password');
    }
}
