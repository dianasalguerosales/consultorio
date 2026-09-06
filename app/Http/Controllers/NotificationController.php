<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notificaciones = Notification::where('user_id', auth()->id())
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
        ]);
    }
}