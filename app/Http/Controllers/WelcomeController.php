<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index() {
        $breadcrumbs = (object) [
            'title' => 'Selamat Datang',
            'list' => ['Home', 'Welome']
        ];

        $activeMenu = 'dashboard';

        return view('Welcome', ['breadcrumb' => $breadcrumbs, 'activeMenu' => $activeMenu]);
    }
}