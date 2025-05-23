<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserLoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLoginActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $loginActivity = new UserLoginActivity();
        $this->assertEquals([
            'user_id',
            'ip_address',
            'location',
            'user_agent',
            'login_method'
        ], $loginActivity->getFillable());
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $user = User::factory()->create();
        $loginActivity = UserLoginActivity::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $loginActivity->user);
        $this->assertEquals($user->id, $loginActivity->user->id);
    }

    /** @test */
    public function it_has_timestamps()
    {
        $loginActivity = UserLoginActivity::factory()->create();

        $this->assertNotNull($loginActivity->created_at);
        $this->assertNotNull($loginActivity->updated_at);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $loginActivity = new UserLoginActivity();
        $this->assertEquals('user_login_activities', $loginActivity->getTable());
    }
}
