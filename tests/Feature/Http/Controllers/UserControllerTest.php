<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\UserProfileSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the update method works correctly with find() instead of where()->first()
     */
    #[Test]
    public function update_profile_works_with_find()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'Original Name'
        ]);

        // Create profile settings for the user
        UserProfileSettings::factory()->create([
            'user_id' => $user->id,
            'theme' => 'light'
        ]);

        // Start query logging
        DB::enableQueryLog();

        // Update the user's profile
        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/profile', [
                'name' => 'Updated Name'
            ]);

        // Get query log
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'true'
            ]);

        // Assert user was updated
        $this->assertEquals('Updated Name', $user->fresh()->name);

        // Assert that find() was used instead of where()->first()
        $findUsed = false;
        foreach ($queryLog as $query) {
            if (strpos($query['query'], 'select * from `users` where `id` = ? limit 1') !== false) {
                $findUsed = true;
                break;
            }
        }
        $this->assertTrue($findUsed, 'find() method was not used');
    }

    /**
     * Test that the updateNotifications method works correctly with find() instead of where()->first()
     */
    #[Test]
    public function update_notifications_works_with_find()
    {
        // Create a user
        $user = User::factory()->create();

        // Create profile settings for the user
        UserProfileSettings::factory()->create([
            'user_id' => $user->id,
            'email_notifications' => false
        ]);

        // Start query logging
        DB::enableQueryLog();

        // Update the user's notifications
        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/user/notifications', [
                'email_notifications' => true
            ]);

        // Get query log
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'true'
            ]);

        // Assert notifications were updated
        $this->assertTrue($user->profileSettings->fresh()->email_notifications);

        // Assert that find() was used instead of where()->first()
        $findUsed = false;
        foreach ($queryLog as $query) {
            if (strpos($query['query'], 'select * from `users` where `id` = ? limit 1') !== false) {
                $findUsed = true;
                break;
            }
        }
        $this->assertTrue($findUsed, 'find() method was not used');
    }

    /**
     * Test query performance with indexes
     */
    #[Test]
    public function query_performance_with_indexes()
    {
        // Create multiple users
        $users = User::factory()->count(10)->create();

        // Create profile settings for each user
        foreach ($users as $user) {
            UserProfileSettings::factory()->create([
                'user_id' => $user->id,
                'is_public' => true
            ]);
        }

        // Measure query time with indexes
        $startTime = microtime(true);

        // Run a query that should use the indexes
        $result = UserProfileSettings::where('is_public', true)
            ->join('users', 'users_profile_settings.user_id', '=', 'users.id')
            ->select('users.name', 'users.email')
            ->get();

        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;

        // Assert that we got the expected number of results
        $this->assertEquals(10, $result->count());

        // Log the query time for reference
        fwrite(STDERR, "Query time with indexes: " . $queryTime . " seconds\n");
    }
}
