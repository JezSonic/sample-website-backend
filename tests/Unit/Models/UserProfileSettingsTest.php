<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserProfileSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $profileSettings = new UserProfileSettings();
        $this->assertEquals([
            'user_id',
            'theme',
            'language',
            'timezone',
            'id',
            'avatar_source',
            'is_public',
            'email_notifications',
            'email_marketing',
            'email_security_alerts'
        ], $profileSettings->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $profileSettings = new UserProfileSettings();
        $this->assertFalse($profileSettings->timestamps);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $profileSettings = new UserProfileSettings();
        $this->assertEquals('users_profile_settings', $profileSettings->getTable());
    }

    /** @test */
    public function it_has_default_values()
    {
        $user = User::factory()->create();
        $profileSettings = UserProfileSettings::factory()->create([
            'user_id' => $user->id
        ]);

        // Refresh from database to get default values
        $profileSettings = $profileSettings->fresh();

        // Check default values from migration
        $this->assertEquals('dark', $profileSettings->theme);
        $this->assertEquals('en', $profileSettings->language);
        $this->assertEquals('Europe/London', $profileSettings->timezone);
        $this->assertTrue((bool)$profileSettings->is_public);
        $this->assertFalse((bool)$profileSettings->email_notifications);
        $this->assertFalse((bool)$profileSettings->email_marketing);
        $this->assertFalse((bool)$profileSettings->email_security_alerts);
    }
}
