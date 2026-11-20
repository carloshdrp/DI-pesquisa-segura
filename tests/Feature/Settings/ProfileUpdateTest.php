<?php

use App\Livewire\Settings\Profile;
use App\Models\Usuario;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = Usuario::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->nm_usuario)->toEqual('Test User');
    expect($user->ds_email)->toEqual('test@example.com');
});

test('profile can be saved keeping the same email address', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->ds_email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();
});

test('user can delete their account', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = Usuario::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
