<?php

namespace Tests\Feature;

use App\Models\FamilyGroup;
use App\Models\User;
use App\Services\ArtisamNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArtisamNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_returns_false_when_api_key_is_missing(): void
    {
        Config::set('services.artisam.api_key', null);

        Http::fake();

        $service = new ArtisamNotificationService();
        $result = $service->send('Test Title', 'Test Message');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_service_sends_notification_successfully_when_configured(): void
    {
        Config::set('services.artisam.base_url', 'https://notifications.artisam.dev');
        Config::set('services.artisam.api_key', 'artisam_live_mock_key_1234567890');

        Http::fake([
            'https://notifications.artisam.dev/api/v1/notify' => Http::response([
                'success' => true,
                'status' => 1,
                'notification_id' => 101,
                'total_registered_devices' => 1,
            ], 201),
        ]);

        $service = new ArtisamNotificationService();
        $result = $service->send(
            title: 'Perfil actualizado',
            message: 'Juan Pérez modificó su perfil.',
            options: [
                'url' => 'https://secret-santa.test/admin/users/1/profile',
                'url_title' => 'Ver perfil',
                'category' => 'profile_update',
            ]
        );

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://notifications.artisam.dev/api/v1/notify'
                && $request->hasHeader('Authorization', 'Bearer artisam_live_mock_key_1234567890')
                && $request['title'] === 'Perfil actualizado'
                && $request['message'] === 'Juan Pérez modificó su perfil.'
                && $request['url'] === 'https://secret-santa.test/admin/users/1/profile'
                && $request['category'] === 'profile_update';
        });
    }

    public function test_profile_update_triggers_admin_notification(): void
    {
        Config::set('services.artisam.base_url', 'https://notifications.artisam.dev');
        Config::set('services.artisam.api_key', 'artisam_live_mock_key_1234567890');

        Http::fake([
            'https://notifications.artisam.dev/api/v1/notify' => Http::response(['success' => true], 201),
        ]);

        $family = FamilyGroup::create([
            'slug' => 'garcia',
            'name' => 'Familia García',
            'is_active' => true,
            'year' => (int) date('Y'),
        ]);

        $user = User::factory()->create([
            'name' => 'Carlos García',
            'nickname' => 'Carlitos',
            'family_group_id' => $family->id,
        ]);
        $family->users()->attach($user->id);

        $this->actingAs($user);

        $response = $this->put(route('user.update', $user), [
            'name' => 'Carlos García Modificado',
            'gift_suggestions' => ['Un libro de cocina'],
        ]);

        $response->assertRedirect(route('user.profile'));

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://notifications.artisam.dev/api/v1/notify'
                && $request['title'] === 'Perfil actualizado'
                && str_contains($request['message'], 'Carlitos')
                && str_contains($request['message'], 'Familia García')
                && $request['url'] === route('admin.users.edit', $user->id)
                && $request['url_title'] === 'Ver perfil'
                && $request['category'] === 'profile_update';
        });
    }
}
