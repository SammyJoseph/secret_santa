<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the specific user
        $user = User::factory()->withGiftSuggestions(3)->create([
            'name' => 'Sam',
            'dni' => '47003307',
            'email' => 'sam@artisam.dev',
            'is_admin' => true,
            'password' => bcrypt(env('ADMIN_DEFAULT_PASSWORD')),
        ]);

        $this->downloadAndSaveProfileImage($user);

        // Create 10 random users only in local environment
        if (app()->environment('local')) {
            User::factory(10)->withGiftSuggestions(3)->create()->each(function ($user) {
                $this->downloadAndSaveProfileImage($user);
            });
        }
    }

    /**
     * Download a random profile image and save it for the user.
     */
    private function downloadAndSaveProfileImage(User $user): void
    {
        try {
            // Use Lorem Picsum for a random image (400x400)
            $imageUrl = 'https://picsum.photos/400/400?random=' . $user->id;

            // Download the image
            $response = Http::get($imageUrl);

            if ($response->successful()) {
                // Generate a unique filename
                $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.jpg';

                // Save to storage/app/public
                Storage::disk('public')->put($filename, $response->body());

                // Update user's profile_photo_path
                $user->update(['profile_photo_path' => $filename]);
            }
        } catch (\Exception $e) {
            // If download fails, leave profile_photo_path as null
            // You can log the error if needed
        }
    }
}