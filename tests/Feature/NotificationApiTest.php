<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\MealReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_list_notifications(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));
        $this->user->notify(new MealReminderNotification('lunch'));

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'notifications',
                    'unread_count',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);

        $this->assertEquals(2, $response->json('data.unread_count'));
    }

    public function test_user_can_filter_unread_notifications(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));
        $this->user->notify(new MealReminderNotification('lunch'));

        // Mark one as read
        $this->user->notifications->first()->markAsRead();

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications?unread_only=1');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.unread_count'));
    }

    public function test_user_can_get_unread_count(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));
        $this->user->notify(new MealReminderNotification('lunch'));
        $this->user->notify(new MealReminderNotification('dinner'));

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/unread-count');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'unread_count' => 3,
                ],
            ]);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));

        $notification = $this->user->notifications->first();

        $response = $this->actingAs($this->user)
            ->putJson("/api/notifications/{$notification->id}/read");

        $response->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_as_read_returns_404_for_invalid_notification(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/invalid-uuid/read');

        $response->assertNotFound();
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));
        $this->user->notify(new MealReminderNotification('lunch'));
        $this->user->notify(new MealReminderNotification('dinner'));

        $this->assertEquals(3, $this->user->unreadNotifications()->count());

        $response = $this->actingAs($this->user)
            ->postJson('/api/notifications/read-all');

        $response->assertOk();

        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));

        $notification = $this->user->notifications->first();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertOk();

        $this->assertEquals(0, $this->user->fresh()->notifications()->count());
    }

    public function test_delete_returns_404_for_invalid_notification(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/notifications/invalid-uuid');

        $response->assertNotFound();
    }

    public function test_user_can_delete_all_read_notifications(): void
    {
        $this->user->notify(new MealReminderNotification('breakfast'));
        $this->user->notify(new MealReminderNotification('lunch'));
        $this->user->notify(new MealReminderNotification('dinner'));

        // Mark two as read
        $this->user->notifications[0]->markAsRead();
        $this->user->notifications[1]->markAsRead();

        $response = $this->actingAs($this->user)
            ->deleteJson('/api/notifications/read');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'deleted_count' => 2,
                ],
            ]);

        $this->assertEquals(1, $this->user->fresh()->notifications()->count());
    }

    public function test_user_cannot_access_other_users_notifications(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->notify(new MealReminderNotification('breakfast'));

        $notification = $otherUser->notifications->first();

        $response = $this->actingAs($this->user)
            ->putJson("/api/notifications/{$notification->id}/read");

        $response->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertUnauthorized();
    }
}
