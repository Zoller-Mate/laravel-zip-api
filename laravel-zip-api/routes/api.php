<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostalCodeController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{id}', [PostalCodeController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{id}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{id}', [PostalCodeController::class, 'destroy']);
});

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    return ['token' => $user->createToken('api')->plainTextToken];
});

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'confirmed', Password::defaults()],
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    return [
        'user' => $user,
        'token' => $user->createToken('api')->plainTextToken
    ];
});
