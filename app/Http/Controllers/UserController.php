<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        Mail::to("test@example.com")->send(new TestMail());
        return inertia('Users/Index');
    }

    public function show($id)
    {
        return inertia('Users/Show', [
            'userId' => $id,
        ]);
    }
}
