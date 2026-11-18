<?php

use App\Models\User;

test('pages render in arabic right-to-left', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl"', escape: false);
});

test('failed login shows an arabic error message', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(['email' => 'بيانات الدخول غير صحيحة.']);
});

test('validation messages use arabic attribute names', function () {
    $user = User::factory()->create();

    $this->post(route('register.store'), [
        'name' => 'عميل جديد',
        'email' => $user->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['email' => 'قيمة البريد الإلكتروني مستخدمة من قبل.']);
});
