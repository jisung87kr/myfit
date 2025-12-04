<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\DietPlanController;
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\ExerciseLogController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\MealLogController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\WeightLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Password Reset
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// Social Authentication
Route::prefix('auth')->group(function () {
    Route::get('/{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::post('/{provider}/callback', [SocialAuthController::class, 'callback']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile Management
    Route::prefix('user')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'changePassword']);
        Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
        Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto']);
    });

    // Social Account Management
    Route::prefix('auth/social')->group(function () {
        Route::get('/accounts', [SocialAuthController::class, 'connectedAccounts']);
        Route::delete('/{provider}', [SocialAuthController::class, 'disconnect']);
    });

    // Survey Management
    Route::prefix('surveys')->group(function () {
        Route::get('/', [SurveyController::class, 'index']);
        Route::get('/{survey}/questions', [SurveyController::class, 'getQuestions']);
        Route::post('/{survey}/answers', [SurveyController::class, 'submitAnswers']);
        Route::get('/{survey}/responses', [SurveyController::class, 'getResponses']);
        Route::get('/{survey}/progress', [SurveyController::class, 'getProgress']);
        Route::get('/{survey}/summary', [SurveyController::class, 'getSummary']);
        Route::get('/{survey}/status', [SurveyController::class, 'getStatus']);
        Route::delete('/{survey}/answers/{question}', [SurveyController::class, 'deleteAnswer']);
        Route::delete('/{survey}/reset', [SurveyController::class, 'resetSurvey']);
        Route::delete('/{survey}/steps/{step}', [SurveyController::class, 'deleteStepResponses']);

        // Survey Submission
        Route::post('/{survey}/submit', [SurveyController::class, 'submit']);
        Route::get('/{survey}/submission', [SurveyController::class, 'getSubmission']);
    });

    // Calorie Calculations
    Route::prefix('calculations')->group(function () {
        Route::post('/bmr', [CalculationController::class, 'calculateBMR']);
        Route::post('/tdee', [CalculationController::class, 'calculateTDEE']);
        Route::post('/target-calories', [CalculationController::class, 'calculateTargetCalories']);
        Route::post('/calculate', [CalculationController::class, 'calculate']);
        Route::get('/latest', [CalculationController::class, 'getLatest']);
    });

    // Foods Management
    Route::prefix('foods')->group(function () {
        Route::get('/', [FoodController::class, 'index']);
        Route::get('/categories', [FoodController::class, 'categories']);
        Route::get('/{food}', [FoodController::class, 'show']);
        Route::post('/', [FoodController::class, 'store']);
        Route::put('/{food}', [FoodController::class, 'update']);
        Route::delete('/{food}', [FoodController::class, 'destroy']);
    });

    // Exercises Management
    Route::prefix('exercises')->group(function () {
        Route::get('/', [ExerciseController::class, 'index']);
        Route::get('/categories', [ExerciseController::class, 'categories']);
        Route::get('/intensities', [ExerciseController::class, 'intensities']);
        Route::get('/{exercise}', [ExerciseController::class, 'show']);
        Route::post('/', [ExerciseController::class, 'store']);
        Route::put('/{exercise}', [ExerciseController::class, 'update']);
        Route::delete('/{exercise}', [ExerciseController::class, 'destroy']);
        Route::post('/{exercise}/calculate-calories', [ExerciseController::class, 'calculateCalories']);
    });

    // Diet Plans
    Route::prefix('diet-plans')->group(function () {
        Route::post('/generate', [DietPlanController::class, 'generate']);
        Route::get('/generation-status/{id}', [DietPlanController::class, 'generationStatus']);
        Route::get('/active', [DietPlanController::class, 'getActive']);
        Route::get('/{id}', [DietPlanController::class, 'show']);
        Route::get('/{id}/day/{day}', [DietPlanController::class, 'showDay']);
        Route::post('/{id}/regenerate', [DietPlanController::class, 'regenerate']);

        // Meal replacement
        Route::put('/meals/{mealItemId}/replace', [DietPlanController::class, 'replaceMealItem']);
        Route::get('/meals/{mealItemId}/suggestions', [DietPlanController::class, 'getMealReplacementSuggestions']);

        // Exercise replacement
        Route::put('/exercises/{exerciseId}/replace', [DietPlanController::class, 'replaceExercise']);
        Route::get('/exercises/{exerciseId}/suggestions', [DietPlanController::class, 'getExerciseReplacementSuggestions']);
    });

    // Daily Meal Logging
    Route::prefix('daily-logs/meals')->group(function () {
        Route::get('/', [MealLogController::class, 'index']);
        Route::post('/', [MealLogController::class, 'store']);
        Route::put('/{id}', [MealLogController::class, 'update']);
        Route::delete('/{id}', [MealLogController::class, 'destroy']);
        Route::get('/summary', [MealLogController::class, 'summary']);
        Route::post('/from-plan', [MealLogController::class, 'logFromPlan']);
    });

    // Daily Exercise Logging
    Route::prefix('daily-logs/exercises')->group(function () {
        Route::get('/', [ExerciseLogController::class, 'index']);
        Route::post('/', [ExerciseLogController::class, 'store']);
        Route::put('/{id}', [ExerciseLogController::class, 'update']);
        Route::delete('/{id}', [ExerciseLogController::class, 'destroy']);
        Route::get('/summary', [ExerciseLogController::class, 'summary']);
        Route::post('/from-plan', [ExerciseLogController::class, 'logFromPlan']);
    });

    // Weight Logging
    Route::prefix('weight-logs')->group(function () {
        Route::get('/show', [WeightLogController::class, 'show']);
        Route::post('/', [WeightLogController::class, 'store']);
        Route::put('/{id}', [WeightLogController::class, 'update']);
        Route::delete('/{id}', [WeightLogController::class, 'destroy']);
        Route::get('/latest', [WeightLogController::class, 'latest']);
        Route::get('/history', [WeightLogController::class, 'history']);
        Route::get('/progress', [WeightLogController::class, 'progress']);
        Route::get('/statistics', [WeightLogController::class, 'statistics']);
    });
});

