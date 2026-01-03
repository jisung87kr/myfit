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
     * Get the survey responses for the user.
     */
    public function surveyResponses()
    {
        return $this->hasMany(UserSurveyResponse::class);
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
}
