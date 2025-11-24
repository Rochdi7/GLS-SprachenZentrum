<?php

use Illuminate\Support\Facades\Auth;

function user_avatar()
{
    $user = Auth::user();

    if (!$user) {
        return asset('assets/images/user/avatar-2.jpg');
    }

    $user = Auth::user();
    $media = $user?->getFirstMedia('profile_photo');

    return $media
        ? $media->getUrl()
        : asset('assets/images/user/avatar-2.jpg');
}
