<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\DailyDashboardController;
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
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\FriendshipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\FoodController as AdminFoodController;
use App\Http\Controllers\Api\Admin\ExerciseController as AdminExerciseController;
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
        Route::get('/{survey}/responses', [SurveyController::class, 'getResponses']);
        Route::get('/{survey}/summary', [SurveyController::class, 'getSummary']);
        Route::get('/{survey}/status', [SurveyController::class, 'getStatus']);
        Route::delete('/{survey}/reset', [SurveyController::class, 'resetSurvey']);
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
        Route::get('/', [DietPlanController::class, 'index']);
        Route::post('/generate', [DietPlanController::class, 'generate']);
        Route::get('/generation-status/{id}', [DietPlanController::class, 'generationStatus']);
        Route::get('/active', [DietPlanController::class, 'getActive']);
        Route::get('/{id}', [DietPlanController::class, 'show']);
        Route::delete('/{id}', [DietPlanController::class, 'destroy']);
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
        Route::get('/summary', [ExerciseLogController::class, 'summary']);
        Route::post('/from-plan', [ExerciseLogController::class, 'logFromPlan']);
        Route::get('/{id}', [ExerciseLogController::class, 'show']);
        Route::put('/{id}', [ExerciseLogController::class, 'update']);
        Route::delete('/{id}', [ExerciseLogController::class, 'destroy']);
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

    // Daily Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/show', [DailyDashboardController::class, 'show']);
        Route::get('/today', [DailyDashboardController::class, 'today']);
        Route::get('/weekly-summary', [DailyDashboardController::class, 'weeklySummary']);
        Route::get('/monthly-summary', [DailyDashboardController::class, 'monthlySummary']);
        Route::get('/quick-stats', [DailyDashboardController::class, 'quickStats']);
        Route::get('/streaks', [DailyDashboardController::class, 'streaks']);
    });

    // Badges
    Route::prefix('badges')->group(function () {
        Route::get('/', [BadgeController::class, 'index']);
        Route::get('/earned', [BadgeController::class, 'earned']);
        Route::get('/categories', [BadgeController::class, 'categories']);
        Route::post('/check', [BadgeController::class, 'check']);
        Route::get('/{badge}', [BadgeController::class, 'show']);
    });

    // Notification Settings
    Route::prefix('notifications/settings')->group(function () {
        Route::get('/', [NotificationSettingController::class, 'show']);
        Route::put('/', [NotificationSettingController::class, 'update']);
        Route::post('/reset', [NotificationSettingController::class, 'reset']);
        Route::post('/toggle', [NotificationSettingController::class, 'toggle']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/read', [NotificationController::class, 'deleteRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Community - Posts
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/categories', [PostController::class, 'categories']);
        Route::get('/my', [PostController::class, 'myPosts']);
        Route::get('/{post}', [PostController::class, 'show']);
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
        Route::post('/{post}/like', [PostController::class, 'like']);
        Route::delete('/{post}/like', [PostController::class, 'unlike']);

        // Comments
        Route::get('/{post}/comments', [CommentController::class, 'index']);
        Route::post('/{post}/comments', [CommentController::class, 'store']);
    });

    // Comments
    Route::prefix('comments')->group(function () {
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
        Route::post('/{comment}/like', [CommentController::class, 'like']);
        Route::delete('/{comment}/like', [CommentController::class, 'unlike']);
    });

    // Challenges
    Route::prefix('challenges')->group(function () {
        Route::get('/', [ChallengeController::class, 'index']);
        Route::get('/my', [ChallengeController::class, 'myChallenges']);
        Route::get('/{challenge}', [ChallengeController::class, 'show']);
        Route::post('/{challenge}/join', [ChallengeController::class, 'join']);
        Route::post('/{challenge}/leave', [ChallengeController::class, 'leave']);
        Route::post('/{challenge}/update-progress', [ChallengeController::class, 'updateProgress']);
        Route::get('/{challenge}/leaderboard', [ChallengeController::class, 'leaderboard']);
    });

    // Friends
    Route::prefix('friends')->group(function () {
        Route::get('/', [FriendshipController::class, 'index']);
        Route::get('/pending', [FriendshipController::class, 'pendingRequests']);
        Route::get('/sent', [FriendshipController::class, 'sentRequests']);
        Route::post('/request', [FriendshipController::class, 'sendRequest']);
        Route::post('/{friendship}/accept', [FriendshipController::class, 'acceptRequest']);
        Route::post('/{friendship}/reject', [FriendshipController::class, 'rejectRequest']);
        Route::delete('/{friendship}/cancel', [FriendshipController::class, 'cancelRequest']);
        Route::delete('/{friend}', [FriendshipController::class, 'removeFriend']);
        Route::post('/{user}/block', [FriendshipController::class, 'blockUser']);
        Route::delete('/{user}/block', [FriendshipController::class, 'unblockUser']);
        Route::get('/{friend}/progress', [FriendshipController::class, 'friendProgress']);
        Route::get('/search', [FriendshipController::class, 'searchUsers']);
    });

    // Admin Routes (requires admin role)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Dashboard & Statistics
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/stats/users', [AdminDashboardController::class, 'userStats']);
        Route::get('/stats/activity', [AdminDashboardController::class, 'activityStats']);

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::get('/{user}', [AdminUserController::class, 'show']);
            Route::put('/{user}/status', [AdminUserController::class, 'updateStatus']);
            Route::get('/{user}/activity', [AdminUserController::class, 'activity']);
            Route::post('/{user}/roles', [AdminUserController::class, 'assignRole']);
            Route::delete('/{user}/roles/{role}', [AdminUserController::class, 'removeRole']);
        });

        // Food Management
        Route::prefix('foods')->group(function () {
            Route::get('/', [AdminFoodController::class, 'index']);
            Route::post('/', [AdminFoodController::class, 'store']);
            Route::get('/{food}', [AdminFoodController::class, 'show']);
            Route::put('/{food}', [AdminFoodController::class, 'update']);
            Route::delete('/{food}', [AdminFoodController::class, 'destroy']);
            Route::post('/bulk-import', [AdminFoodController::class, 'bulkImport']);
        });

        // Exercise Management
        Route::prefix('exercises')->group(function () {
            Route::get('/', [AdminExerciseController::class, 'index']);
            Route::post('/', [AdminExerciseController::class, 'store']);
            Route::get('/{exercise}', [AdminExerciseController::class, 'show']);
            Route::put('/{exercise}', [AdminExerciseController::class, 'update']);
            Route::delete('/{exercise}', [AdminExerciseController::class, 'destroy']);
            Route::post('/bulk-import', [AdminExerciseController::class, 'bulkImport']);
        });
    });
});

