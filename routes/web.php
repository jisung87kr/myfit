<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');

    // Meals
    Route::prefix('meals')->name('meals.')->group(function () {
        Route::get('/', function () {
            return view('meals.index');
        })->name('index');

        Route::get('/create', function () {
            return view('meals.create');
        })->name('create');
    });

    // Exercises
    Route::prefix('exercises')->name('exercises.')->group(function () {
        Route::get('/', function () {
            return view('exercises.index');
        })->name('index');

        Route::get('/create', function () {
            return view('exercises.create');
        })->name('create');
    });

    // Weight
    Route::prefix('weight')->name('weight.')->group(function () {
        Route::get('/', function () {
            return view('weight.index');
        })->name('index');

        Route::get('/create', function () {
            return view('weight.create');
        })->name('create');
    });

    // Diet Plan
    Route::prefix('diet-plan')->name('diet-plan.')->group(function () {
        Route::get('/', function () {
            return view('diet-plan.index');
        })->name('index');
    });

    // Logout (using POST method)
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
