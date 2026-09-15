<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Google_Client;
use Google_Service_Calendar;

class GoogleCalendarServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Google_Service_Calendar::class, function ($app) {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/google-calendar/credentials.json'));
            $client->addScope(Google_Service_Calendar::CALENDAR);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            return new Google_Service_Calendar($client);
        });
    }

    public function boot()
    {
        //
    }
}