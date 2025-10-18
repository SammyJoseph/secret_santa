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

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'dni' => $validated['dni'],
            'email' => $validated['email'] ?? $validated['dni'] . '@secret-santa.local',
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->hasFile('profile_photo_path')) {
            $this->saveProfileImage($user, $request->file('profile_photo_path'));
        } elseif ($request->input('temp_image_filename')) {
            // Move temp image to permanent storage
            $tempFilename = $request->input('temp_image_filename');
            $tempPath = storage_path('app/public/temp/' . $tempFilename);
            if (file_exists($tempPath)) {
                $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.' . pathinfo($tempFilename, PATHINFO_EXTENSION);
                Storage::disk('public')->put($filename, file_get_contents($tempPath));
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

        return redirect()->route('user.profile')->with('success', 'Tu participación fue registrada con éxito. Aquí verás quién es tu Amigo Secreto después del sorteo. Puedes actualizar tu perfil y sugerencias de regalo antes del sorteo.');
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
            // Move temp image to permanent storage
            $tempFilename = $request->input('temp_image_filename');
            $tempPath = storage_path('app/public/temp/' . $tempFilename);
            if (file_exists($tempPath)) {
                $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.' . pathinfo($tempFilename, PATHINFO_EXTENSION);
                Storage::disk('public')->put($filename, file_get_contents($tempPath));
                $user->update(['profile_photo_path' => $filename]);
                // Clean up temp file
                unlink($tempPath);
            }
        }

        // Handle gift suggestions update
        // Delete existing suggestions and create new ones
        $user->giftSuggestions()->delete();

        foreach ($validated['gift_suggestions'] as $suggestion) {
            GiftSuggestion::create([
                'user_id' => $user->id,
                'suggestion' => $suggestion,
            ]);
        }

        session()->forget('temp_profile_image');

        return redirect()->route('user.profile')->with('success', 'Tu perfil se actualizó con éxito.');
    }

    public function profile()
    {
        $user = Auth::user();
        
        $revealDate = new \DateTime('2025-10-17 19:54:00'); // Reveal date for Secret Santa users
        $now = new \DateTime();
        $isRevealed = $now >= $revealDate;

        $secretSanta = null;
        if ($isRevealed) {
            $assignment = SecretSantaAssignment::where('giver_id', $user->id)->with('receiver')->first();
            if ($assignment) {
                $secretSanta = $assignment->receiver;
            }
        }

        // Format date for JavaScript
        $revealDateJs = $revealDate->format('Y-m-d\TH:i:s');

        return view('user.profile', compact('user', 'secretSanta', 'isRevealed', 'revealDateJs'));
    }

    public function tempUpload(Request $request)
    {
        $request->validate([
            'profile_photo_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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

    /**
      * Save the profile image for the user.
      */
     private function saveProfileImage(User $user, $imageFile): void
     {
         try {
             // Generate a unique filename
             $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();

             // Save to storage/app/public
             Storage::disk('public')->put($filename, file_get_contents($imageFile->getRealPath()));

             // Update user's profile_photo_path
             $user->update(['profile_photo_path' => $filename]);
         } catch (\Exception $e) {
             // If upload fails, leave profile_photo_path as null
             // You can log the error if needed
         }
     }
}
