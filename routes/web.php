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
        return view('profile.edit');
    })->name('profile.edit');

    // Survey
    Route::get('/survey', function () {
        return view('survey.index');
    })->name('survey.index');

    // Tools
    Route::prefix('tools')->name('tools.')->group(function () {
        Route::get('/calorie-calculator', function () {
            return view('tools.calorie-calculator');
        })->name('calorie-calculator');
    });

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
    });

    // Diet Plan
    Route::prefix('diet-plan')->name('diet-plan.')->group(function () {
        Route::get('/', function () {
            return view('diet-plan.index');
        })->name('index');
    });

    // Summary
    Route::prefix('summary')->name('summary.')->group(function () {
        Route::get('/weekly', function () {
            return view('summary.weekly');
        })->name('weekly');
    });

    // Logout (using POST method)
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
