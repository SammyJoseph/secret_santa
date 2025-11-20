<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\GiftSuggestion;
use App\Models\SecretSantaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebPEncoder;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // Obtener family_group_id de la sesión (establecido por CaptureFamilyGroup middleware)
        $familyGroupId = session('registration_family_group_id', 1);

        $user = User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'family_group_id' => $familyGroupId,
        ]);

        if ($request->hasFile('profile_photo_path')) {
            $this->saveProfileImage($user, $request->file('profile_photo_path'));
        } elseif ($request->input('temp_image_filename')) {
            // Move temp image to permanent storage with resizing
            $tempFilename = $request->input('temp_image_filename');
            $tempPath = storage_path('app/public/temp/' . $tempFilename);
            if (file_exists($tempPath)) {
                $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.jpg';

                // Resize temp image to 600px width maintaining aspect ratio
                $manager = new ImageManager(new Driver());
                $image = $manager->read($tempPath);
                $image->scale(width: 600);

                // Always save as JPEG to avoid WebP encoder issues
                Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));

                $user->update(['profile_photo_path' => $filename]);
                // Clean up temp file
                unlink($tempPath);
            }
        }

        foreach ($validated['gift_suggestions'] as $suggestion) {
            GiftSuggestion::create([
                'user_id' => $user->id,
                'suggestion' => $suggestion,
            ]);
        }

        session()->forget('temp_profile_image');
        Auth::login($user);

        return redirect()->route('user.profile')->with('success', 'Tu participación fue registrada con éxito. Puedes actualizar tu perfil y tus sugerencias de regalo hasta antes del sorteo.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // Update user data (excluding DNI)
        $user->update([
            'name' => $validated['name'],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo_path')) {
            $this->saveProfileImage($user, $request->file('profile_photo_path'));
        } elseif ($request->input('temp_image_filename')) {
            // Move temp image to permanent storage with resizing
            $tempFilename = $request->input('temp_image_filename');
            $tempPath = storage_path('app/public/temp/' . $tempFilename);
            if (file_exists($tempPath)) {
                $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.jpg';

                // Resize temp image to 600px width maintaining aspect ratio
                $manager = new ImageManager(new Driver());
                $image = $manager->read($tempPath);
                $image->scale(width: 600);

                // Always save as JPEG to avoid WebP encoder issues
                Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));

                $user->update(['profile_photo_path' => $filename]);
                // Clean up temp file
                unlink($tempPath);
            }
        }

        // Handle gift suggestions update
        // Collect old reference images before deleting
        $oldImages = [];
        foreach ($user->giftSuggestions as $index => $suggestion) {
            $oldImages[$index] = $suggestion->reference_image_path;
        }
        
        // Delete existing suggestions and create new ones
        $user->giftSuggestions()->delete();
        
        $tempGiftImages = session('temp_gift_images', []);
        
        foreach ($validated['gift_suggestions'] as $index => $suggestion) {
            $referenceImagePath = null;
        
            // Check if there's a temp image for this suggestion
            if (isset($tempGiftImages[$index])) {
                $tempFilename = $tempGiftImages[$index];
                $tempPath = storage_path('app/public/temp/' . $tempFilename);
        
                if (file_exists($tempPath)) {
                    // Move temp image to permanent storage with resizing
                    $filename = 'gift-suggestions/' . $user->id . '_' . $index . '_' . Str::random(10) . '.jpg';

                    // Resize image to 600px width maintaining aspect ratio
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($tempPath);
                    $image->scale(width: 600);

                    // Always save as JPEG to avoid WebP encoder issues
                    Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));

                    $referenceImagePath = $filename;
                    // Clean up temp file
                    unlink($tempPath);
                }
            } elseif (isset($oldImages[$index])) {
                // Preserve existing image if no new temp image
                $referenceImagePath = $oldImages[$index];
            }
        
            GiftSuggestion::create([
                'user_id' => $user->id,
                'suggestion' => $suggestion,
                'reference_image_path' => $referenceImagePath,
            ]);
        }

        session()->forget('temp_gift_images');
        $user->touch(); // Update the updated_at timestamp of the user

        session()->forget('temp_profile_image');
        session()->forget('temp_gift_images');

        return redirect()->route('user.profile')->with('success', 'Tu perfil se actualizó con éxito.');
    }

    public function profile()
    {
        $user = Auth::user();
        $familyGroup = $user->familyGroup;

        // Usar fechas de la familia del usuario
        $now = new \DateTime();
        $isRevealed = $familyGroup->isRevealed();
        $canEditProfile = $familyGroup->canEditProfile();

        $secretSanta = null;
        if ($isRevealed) {
            $assignment = SecretSantaAssignment::where('giver_id', $user->id)
                ->where('family_group_id', $user->family_group_id)
                ->with('receiver')
                ->first();
            
            if ($assignment) {
                $secretSanta = $assignment->receiver;
                
                // Format gift suggestions to make URLs clickable
                foreach ($secretSanta->giftSuggestions as $suggestion) {
                    $suggestion->formatted_suggestion = $this->formatSuggestion($suggestion->suggestion);
                }
            }
        }

        // Format dates for JavaScript
        $revealDateJs = $familyGroup->reveal_date ? $familyGroup->reveal_date->format('Y-m-d\TH:i:s') : null;
        $profileEditEndDateJs = $familyGroup->profile_edit_end_date ? $familyGroup->profile_edit_end_date->format('Y-m-d\TH:i:s') : null;

        return view('user.profile', compact('user', 'secretSanta', 'isRevealed', 'revealDateJs', 'canEditProfile', 'profileEditEndDateJs', 'familyGroup'));
    }

    public function tempUpload(Request $request)
    {
        $request->validate([
            'profile_photo_path' => 'required|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ]);

        if ($request->hasFile('profile_photo_path')) {
            $file = $request->file('profile_photo_path');
            $filename = 'temp_' . session()->getId() . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Save to storage/app/public/temp
            Storage::disk('public')->put('temp/' . $filename, file_get_contents($file->getRealPath()));

            // Store temp filename in session
            session(['temp_profile_image' => $filename]);

            return response()->json(['filename' => $filename]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function getTempImage($filename)
    {
        $path = storage_path('app/public/temp/' . $filename);

        if (file_exists($path)) {
            return response()->file($path);
        }

        abort(404);
    }

    public function tempUploadGift(Request $request, $index)
    {
        $request->validate([
            'reference_image_path' => 'required|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ]);

        if ($request->hasFile('reference_image_path')) {
            $file = $request->file('reference_image_path');
            $filename = 'temp_gift_' . $index . '_' . session()->getId() . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Save to storage/app/public/temp
            Storage::disk('public')->put('temp/' . $filename, file_get_contents($file->getRealPath()));

            // Store temp filename in session with index
            $tempGiftImages = session('temp_gift_images', []);
            $tempGiftImages[$index] = $filename;
            session(['temp_gift_images' => $tempGiftImages]);

            return response()->json(['filename' => $filename]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function getTempImageGift($filename)
    {
        $path = storage_path('app/public/temp/' . $filename);

        if (file_exists($path)) {
            return response()->file($path);
        }

        abort(404);
    }

    /**
     * Format suggestion text to make URLs clickable.
     */
    private function formatSuggestion($text)
    {
        // Escape the text first to prevent XSS from non-link parts
        $text = e($text);

        // Pattern to find URLs (http, https, or www.)
        $pattern = '/(https?:\/\/|www\.)[^\s]+/';

        return preg_replace_callback($pattern, function($matches) {
            $url = $matches[0];
            
            // Clean trailing punctuation and HTML entities
            $dirty = true;
            while ($dirty) {
                $dirty = false;
                $lastChar = substr($url, -1);
                if (in_array($lastChar, ['.', ',', '!', '?', ')', ']'])) {
                    $url = substr($url, 0, -1);
                    $dirty = true;
                }
                if (str_ends_with($url, '&quot;')) {
                    $url = substr($url, 0, -6);
                    $dirty = true;
                }
                if (str_ends_with($url, '&lt;')) {
                    $url = substr($url, 0, -4);
                    $dirty = true;
                }
                if (str_ends_with($url, '&gt;')) {
                    $url = substr($url, 0, -4);
                    $dirty = true;
                }
            }

            $href = $url;
            if (strpos($url, 'www.') === 0) {
                $href = 'https://' . $url;
            }

            // Parse URL for display
            $parsedUrl = html_entity_decode($href);
            $parsed = parse_url($parsedUrl);
            $host = $parsed['host'] ?? $url;
            
            // Remove www. from display
            $host = preg_replace('/^www\./', '', $host);
            
            // Truncate and add ellipsis
            $display = $host . '...';

            return '<a href="' . $href . '" target="_blank" class="text-blue-600 hover:underline" title="' . $url . '">' . $display . '</a>';
        }, $text);
    }

    /**
      * Save the profile image for the user.
      */
     private function saveProfileImage(User $user, $imageFile): void
     {
         try {
             // Generate a unique filename (always .jpg)
             $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.jpg';

             // Resize image to 600px width maintaining aspect ratio
             $manager = new ImageManager(new Driver());
             $image = $manager->read($imageFile->getRealPath());
             $image->scale(width: 600);

             // Always save as JPEG to avoid WebP encoder issues
             Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));

             // Update user's profile_photo_path
             $user->update(['profile_photo_path' => $filename]);
         } catch (\Exception $e) {
             // If upload fails, leave profile_photo_path as null
             // You can log the error if needed
         }
     }
}
