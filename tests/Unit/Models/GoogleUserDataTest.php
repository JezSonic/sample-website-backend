<?php

namespace Tests\Unit\Models;

use App\Models\GoogleUserData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleUserDataTest extends TestCase {
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes() {
        $googleData = new GoogleUserData();
        $this->assertEquals([
            'user_id',
            'google_name',
            'google_email',
            'google_avatar_url',
            'id',
            'google_token',
            'google_token_expires_in',
            'google_refresh_token'
        ], $googleData->getFillable());
    }

    #[Test]
    public function it_belongs_to_user() {
        $user = User::factory()->create();
        $googleData = GoogleUserData::factory()->create([
            'user_id' => $user->id
        ]);

        $this->assertInstanceOf(User::class, $googleData->user);
        $this->assertEquals($user->id, $googleData->user->id);
    }

    #[Test]
    public function it_has_no_timestamps() {
        $googleData = new GoogleUserData();
        $this->assertFalse($googleData->timestamps);
    }

    #[Test]
    public function it_has_correct_table_name() {
        $googleData = new GoogleUserData();
        $this->assertEquals('google_user_data', $googleData->getTable());
    }
}
