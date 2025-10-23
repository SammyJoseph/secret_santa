<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('secretSantaAssignment.receiver')->orderBy('updated_at', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $users = User::with('secretSantaAssignment.receiver')->orderBy('created_at', 'desc')->get();
        return view('admin.users.edit', compact('user', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'funny_profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        if ($request->hasFile('funny_profile_photo_path')) {
            $this->saveFunnyProfileImage($user, $request->file('funny_profile_photo_path'));
        }

        return redirect()->route('admin.users.edit', $user)->with('success', 'Perfil actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Generate a temporary reset link for the user.
     */
    public function generateResetLink(User $user)
    {
        $token = Str::random(64);
        $expiresAt = now()->addMinutes(30); // Link expires in 30 minutes

        $user->update([
            'reset_token' => $token,
            'reset_expires_at' => $expiresAt,
        ]);

        $resetUrl = URL::to('/password/reset/' . $token);

        return response()->json([
            'reset_url' => $resetUrl,
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }


    /**
     * Save the funny profile image for the user.
     */
    private function saveFunnyProfileImage(User $user, $imageFile): void
    {
        try {
            // Generate a unique filename
            $filename = 'funny-profile-photos/' . $user->id . '_' . Str::random(10) . '.' . $imageFile->getClientOriginalExtension();

            // Resize image to 600px width maintaining aspect ratio
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageFile->getRealPath());
            $image->scale(width: 600);

            // Save to storage/app/public
            $extension = strtolower($imageFile->getClientOriginalExtension());
            if ($extension === 'png') {
                Storage::disk('public')->put($filename, $image->encode(new PngEncoder()));
            } else {
                Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));
            }

            // Update user's funny_profile_photo_path
            $user->update(['funny_profile_photo_path' => $filename]);
        } catch (\Exception $e) {
            // If upload fails, leave funny_profile_photo_path as null
            // You can log the error if needed
        }
    }
}
