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

        return Inertia::render('Notificaciones', [
            'notificaciones' => $notificaciones,
        ]);
    }

    /** Se marca al abrirla desde la campana. */
    public function leer(Request $request, Notification $notificacion)
    {
        abort_unless($notificacion->user_id === $request->user()->id, 403);

        $notificacion->update(['leida' => true]);

        return back();
    }

    public function leerTodas(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('leida', false)
            ->update(['leida' => true]);

        return back()->with('success', 'Notificaciones marcadas como leídas.');
    }
}