<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class loginController extends Controller
{
    public function Login(Request $request)
    {
        $validate = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ],
            [
                'email.required' => 'Email is required',
                'email.email' => 'Invalid email format',
                'password.required' => 'Password is required',
                'password.min' => 'Password must be at least 8 characters',
            ]
        );

        if (! Auth::attempt($validate)) {
            return back()->withErrors(['email' => 'Invalid email format'])->onlyInput('email');
        }

        if (Auth::user()->role === 'formateur') {
            return redirect('/absences/saisie')->with(['success' => 'Connexion reussi !']);
        }

        return redirect('/import')->with(['success' => 'Connexion reussi !']);
    }
}
