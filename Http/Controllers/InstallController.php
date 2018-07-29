<?php

namespace App\Http\Controllers;

use Artisan;
use Illuminate\Http\Request;

class InstallController extends Controller
{
    public function index()
    {
        abort_if(env('APP_INSTALLED'), 404);

        Artisan::call('migrate', [
            '--no-interaction' => true,
        ]);

        Artisan::call('db:seed', [
            '--no-interaction' => true,
        ]);

        Artisan::call('telegram:install', [
            '--no-interaction' => true,
        ]);

        setenv('APP_INSTALLED', 'true');
    }

    public function telegram()
    {
        Artisan::call('telegram:install', [
            '--no-interaction' => true,
        ]);
    }
}
