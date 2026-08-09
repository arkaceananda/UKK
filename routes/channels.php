<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('kasir-channel', function () {
    return true;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return true;
});

Broadcast::channel('stock-updates', function () {
    return true;
});
