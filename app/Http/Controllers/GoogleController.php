<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Support\Facades\Log;


class GoogleController extends Controller
{
    public function redirect()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $client->addScope(Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
        $client->addScope(Calendar::CALENDAR);

        $code = $request->query('code');
        if (!$code) {
            \Log::error('No se recibió el parámetro code en el callback');
            return redirect()->route('agenda.index')->withErrors('No se recibió el código de Google.');
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            \Log::error('Error al obtener token', $token);
            return redirect()->route('agenda.index')->withErrors('Error al conectar con Google.');
        }

        if (!empty($token['access_token'])) {
            auth()->user()->update([
                'google_token' => json_encode($token),
            ]);
            \Log::info('Token guardado en BD para usuario:', ['id' => auth()->id()]);
        } else {
            \Log::error('Token vacío o inválido', $token);
        }

        return redirect()->route('agenda.index')
            ->with('success', 'Google Calendar conectado correctamente.');
    }

}