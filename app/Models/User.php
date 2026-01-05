<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'profile_photo_path',
        'weight',
        'height',
        'disabled_at',
        'disabled_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the social accounts for the user.
     */
    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * Get the survey submissions for the user.
     */
    public function surveySubmissions()
    {
        return $this->hasMany(SurveySubmission::class);
    }

    /**
     * Get the user's calculations.
     */
    public function calculations()
    {
        return $this->hasMany(UserCalculation::class);
    }

    /**
     * Get the user's badges.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot(['earned_at', 'progress_value'])
            ->withTimestamps();
    }

    /**
     * Get the user's earned badges.
     */
    public function earnedBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get the user's notification settings.
     */
    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    /**
     * Get or create notification settings.
     */
    public function getNotificationSettings(): NotificationSetting
    {
        return $this->notificationSettings ?? $this->notificationSettings()->create(
            NotificationSetting::getDefaults()
        );
    }

    /**
     * Get the user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the user's likes.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get challenges the user is participating in.
     */
    public function challengeParticipations()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    /**
     * Get the user's friends (sent requests that were accepted).
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Get friend requests received by the user.
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Get all friends (both directions, accepted only).
     */
    public function friends()
    {
        $sentFriends = $this->sentFriendRequests()
            ->where('status', 'accepted')
            ->pluck('friend_id');

        $receivedFriends = $this->receivedFriendRequests()
            ->where('status', 'accepted')
            ->pluck('user_id');

        return User::whereIn('id', $sentFriends->merge($receivedFriends));
    }

    /**
     * Check if user is friends with another user.
     */
    public function isFriendsWith(User $user): bool
    {
        return Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $this->id)->where('friend_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('friend_id', $this->id);
        })->where('status', 'accepted')->exists();
    }

    /**
     * Get the user's meal logs.
     */
    public function mealLogs()
    {
        return $this->hasMany(MealLog::class);
    }

    /**
     * Get the user's exercise logs.
     */
    public function exerciseLogs()
    {
        return $this->hasMany(ExerciseLog::class);
    }

    /**
     * Get the user's weight logs.
     */
    public function weightLogs()
    {
        return $this->hasMany(WeightLog::class);
    }
}
