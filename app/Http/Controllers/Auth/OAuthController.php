<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    // Redirige al usuario a la página de autorización de GitHub
    public function redirect()
    {
        return Socialite::driver('github')->redirect();
    }

    // Recibe la respuesta de GitHub
    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Hubo un error al autenticar con GitHub.']);
        }

        // Buscamos si ya existe un usuario con ese github_id o con el mismo email
        $user = User::where('github_id', $githubUser->id)
                    ->orWhere('email', $githubUser->getEmail())
                    ->first();

        if ($user) {
            // Si ya existe, nos aseguramos de que tenga enlazado su github_id
            $user->update(['github_id' => $githubUser->id]);
        } else {
            // Si es un usuario nuevo, lo registramos en la base de datos
            $user = User::create([
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
                'github_id' => $githubUser->id,
                'password' => bcrypt(Str::random(16)), // Contraseña aleatoria segura
            ]);
        }

        // Iniciamos la sesión del usuario
        Auth::login($user);

        // Lo mandamos al catálogo de la tienda
        return redirect()->route('products.index');
    }
}