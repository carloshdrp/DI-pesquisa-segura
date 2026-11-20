<?php

use App\Models\Usuario;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $usuario = Usuario::where('ds_email', 'test@example.com')->firstOrFail();

    expect($usuario->nm_usuario)->toBe('John Doe')
        ->and($usuario->tp_perfil)->toBeFalse()
        ->and($usuario->nr_limite_busca_dia)->toBe(config('busca.limite_autenticado'))
        ->and($usuario->nr_buscas_hoje)->toBe(0)
        ->and($usuario->ds_senha)->not->toBe('password');
});
