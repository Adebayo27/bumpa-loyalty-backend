<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for in-app "reverb" notifications per user
Broadcast::channel('reverb.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
