<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        // Traemos todos los usuarios y contamos los recursos públicos
        $users = User::withCount(['resources' => function ($query) {
            $query->where('privacy', 'public');
        }])->get();

        return view('users.index', compact('users'));
    }
}