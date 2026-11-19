<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\GiftSuggestion;
use App\Models\GiftSuggestionHistory;
use App\Models\SecretSantaAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebPEncoder;
use App\Services\ArtisamNotificationService;

class UserController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        $targetFamilyId = session('registration_family_group_id');
        $targetFamily = null;

        if ($targetFamilyId) {
            $targetFamily = \App\Models\FamilyGroup::find($targetFamilyId);
        }

        if (!$targetFamily && $request->has('fam')) {
            $targetFamily = \App\Models\FamilyGroup::where('slug', $request->fam)->where('is_active', true)->first();
        }

        if (!$targetFamily) {
            $targetFamily = \App\Models\FamilyGroup::find(1) ?? \App\Models\FamilyGroup::where('is_active', true)->orderBy('year', 'desc')->first();
        }

        if (Auth::check()) {
            $user = Auth::user();

            if (!$targetFamily) {
                return redirect()->route('user.profile')->with('error', 'La familia especificada no es válida.');
            }

            if ($targetFamily->hasDrawn()) {
                return redirect()->route('user.profile')->with('error', "No es posible unirse a '{$targetFamily->name}' porque el sorteo ya fue realizado.");
            }

            // Si ya pertenece a esta misma familia, solo redirigir a su perfil
            if ($user->family_group_id === $targetFamily->id) {
                return redirect()->route('user.profile');
            }

            // Si ya está en otra familia del mismo año
            if ($user->familyGroup && $user->familyGroup->year === $targetFamily->year && $user->familyGroup->id !== $targetFamily->id) {
                if ($user->familyGroup->hasDrawn()) {
                    return redirect()->route('user.profile')->with('error', "Ya estás participando en '{$user->familyGroup->name}' este año y su sorteo ya fue realizado.");
                }
            }

            // Auto-asignación a la nueva familia (Opción A)
            // Vinculamos en la tabla pivote para preservar membresías de familias anteriores
            $targetFamily->users()->syncWithoutDetaching([$user->id]);

            $user->update([
                'family_group_id' => $targetFamily->id,
                'family_id' => null, // resetear exclusión de sorteos anteriores
            ]);

            return redirect()->route('user.profile')->with('success', "¡Te has unido exitosamente a {$targetFamily->name} ({$targetFamily->year})! Por favor completa tus 3 sugerencias de regalo para esta nueva edición.");
        }

        $familyGroup = $targetFamily;

        return view('user.register', compact('familyGroup', 'targetFamily'));
    }

    public function checkIdentifier(string $identifier)
    {
        $identifier = trim($identifier);

        $user = User::where('phone', $identifier)
                    ->orWhere('dni', $identifier)
                    ->with(['familyGroup', 'familyGroups'])
                    ->first();

        if (!$user) {
            return response()->json([
                'exists' => false,
                'identifier' => $identifier,
            ]);
        }

        $targetFamilyId = session('registration_family_group_id', 1);
        $targetFamily = \App\Models\FamilyGroup::find($targetFamilyId);
        $targetYear = $targetFamily ? $targetFamily->year : (int) date('Y');

        $alreadyRegisteredThisYear = false;
        $registeredFamilyName = null;

        $currentYearFamily = $user->familyGroups()->where('year', $targetYear)->first()
            ?? ($user->familyGroup && $user->familyGroup->year == $targetYear ? $user->familyGroup : null);

        if ($currentYearFamily) {
            $alreadyRegisteredThisYear = true;
            $registeredFamilyName = $currentYearFamily->name;
        }

        $matchedBy = ($user->dni === $identifier) ? 'dni' : 'phone';

        return response()->json([
            'exists' => true,
            'matched_by' => $matchedBy,
            'name' => $user->name,
            'dni' => $user->dni,
            'phone' => $user->phone,
            'photo_url' => $user->profile_photo_url,
            'already_registered_this_year' => $alreadyRegisteredThisYear,
            'registered_family_name' => $registeredFamilyName,
        ]);
    }

    public function checkDni(string $dni)
    {
        return $this->checkIdentifier($dni);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // Obtener family_group_id de la sesión (establecido por CaptureFamilyGroup middleware)
        $familyGroupId = session('registration_family_group_id', 1);

        $user = null;
        $rawIdentifier = $validated['phone'];
        $migratedDni = $request->input('migrated_dni');

        if (!empty($migratedDni)) {
            $user = User::where('dni', $migratedDni)->first();
        }

        // Si el identificador es de 8 dígitos, puede ser un DNI
        if (!$user && strlen($rawIdentifier) === 8) {
            $user = User::where('dni', $rawIdentifier)->first();
        }

        if (!$user) {
            $user = User::where('phone', $rawIdentifier)->first();
        }

        if ($user) {
            // Usuario ya registrado anteriormente en la base de datos (o migrando desde DNI)
            $updateData = [
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
                'family_group_id' => $familyGroupId,
                'family_id' => null,
            ];

            if (strlen($rawIdentifier) === 9) {
                $updateData['phone'] = $rawIdentifier;
            } elseif (strlen($rawIdentifier) === 8 && empty($user->dni)) {
                $updateData['dni'] = $rawIdentifier;
            }

            if (!empty($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }

            $user->update($updateData);

            $existingSuggestions = GiftSuggestion::where('user_id', $user->id)
                ->where('family_group_id', $familyGroupId)
                ->orderBy('id')
                ->get();

            $previousSnapshot = $this->formatSuggestionsSnapshot($existingSuggestions);
            $incomingSnapshot = [];
            foreach (($validated['gift_suggestions'] ?? []) as $suggestion) {
                $suggestionText = is_string($suggestion) ? trim($suggestion) : '';
                if ($suggestionText !== '') {
                    $incomingSnapshot[] = [
                        'suggestion' => $suggestionText,
                        'reference_image_path' => null,
                    ];
                }
            }

            if (!empty($previousSnapshot) && $previousSnapshot !== $incomingSnapshot) {
                GiftSuggestionHistory::create([
                    'user_id' => $user->id,
                    'family_group_id' => $familyGroupId,
                    'suggestions' => $previousSnapshot,
                    'suggested_at' => $existingSuggestions->max('updated_at')
                        ?? $existingSuggestions->max('created_at'),
                ]);
            }

            // Reemplazar solo sugerencias de la familia actual si existieran (nunca borrar años pasados)
            GiftSuggestion::where('user_id', $user->id)
                ->where('family_group_id', $familyGroupId)
                ->delete();
        } else {
            // Nuevo usuario
            $isNineDigits = (strlen($rawIdentifier) === 9);
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $isNineDigits ? $rawIdentifier : null,
                'dni' => !$isNineDigits ? $rawIdentifier : null,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'family_group_id' => $familyGroupId,
            ]);
        }

        $targetFamily = \App\Models\FamilyGroup::find($familyGroupId);
        if ($targetFamily) {
            $targetFamily->users()->syncWithoutDetaching([$user->id]);
        }

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

        if (!empty($validated['gift_suggestions'])) {
            foreach ($validated['gift_suggestions'] as $suggestion) {
                if (!empty($suggestion)) {
                    GiftSuggestion::create([
                        'user_id' => $user->id,
                        'family_group_id' => $familyGroupId,
                        'suggestion' => $suggestion,
                    ]);
                }
            }
        }

        session()->forget('temp_profile_image');
        Auth::login($user);

        return redirect()->route('user.profile')->with('success', 'Tu participación fue registrada con éxito. Puedes actualizar tu perfil y tus sugerencias de regalo hasta antes del sorteo.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        if (!$user->familyGroup || !$user->familyGroup->canEditProfile()) {
            return back()->with('error', 'Ya no es posible editar tu perfil.');
        }

        $validated = $request->validated();

        // Update user data (excluding DNI)
        $user->update([
            'name' => $validated['name'],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo_path')) {
            $this->saveProfileImage($user, $request->file('profile_photo_path'));
        }

        // Handle gift suggestions update
        // Collect old reference images and existing suggestions FOR CURRENT FAMILY ONLY
        $currentFamilySuggestions = GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $user->family_group_id)
            ->orderBy('id')
            ->get();

        $oldImages = [];
        foreach ($currentFamilySuggestions->values() as $index => $suggestion) {
            $oldImages[$index] = $suggestion->reference_image_path;
        }

        $previousSnapshot = $this->formatSuggestionsSnapshot($currentFamilySuggestions);
        $newSnapshot = [];

        if (!empty($validated['gift_suggestions']) && is_array($validated['gift_suggestions'])) {
            foreach ($validated['gift_suggestions'] as $index => $suggestion) {
                $suggestionText = is_string($suggestion) ? trim($suggestion) : '';
                $hasNewImage = $request->hasFile('reference_image_path_' . $index);
                $isRemoved = $request->boolean('remove_reference_image_' . $index);
                $hasOldImage = !empty($oldImages[$index]) && !$isRemoved;

                // If user didn't write anything and has no image (neither new nor old), skip (empty slot)
                if ($suggestionText === '' && !$hasNewImage && !$hasOldImage) {
                    continue;
                }

                $referenceImagePath = null;

                if ($hasNewImage) {
                    $referenceImagePath = $this->saveGiftImage(
                        $user,
                        (int) $index,
                        $request->file('reference_image_path_' . $index),
                        $oldImages[$index] ?? null
                    );
                } elseif ($hasOldImage) {
                    $referenceImagePath = $oldImages[$index];
                }

                $newSnapshot[] = [
                    'suggestion' => $suggestionText !== '' ? $suggestionText : 'Sugerencia de regalo',
                    'reference_image_path' => $referenceImagePath ?: null,
                ];
            }
        }

        if ($previousSnapshot !== $newSnapshot) {
            DB::transaction(function () use ($user, $currentFamilySuggestions, $previousSnapshot, $newSnapshot) {
                if (!empty($previousSnapshot)) {
                    GiftSuggestionHistory::create([
                        'user_id' => $user->id,
                        'family_group_id' => $user->family_group_id,
                        'suggestions' => $previousSnapshot,
                        'suggested_at' => $currentFamilySuggestions->max('updated_at')
                            ?? $currentFamilySuggestions->max('created_at'),
                    ]);
                }

                // Delete existing suggestions ONLY for the current family (never delete other years/families)
                GiftSuggestion::where('user_id', $user->id)
                    ->where('family_group_id', $user->family_group_id)
                    ->delete();

                foreach ($newSnapshot as $item) {
                    GiftSuggestion::create([
                        'user_id' => $user->id,
                        'family_group_id' => $user->family_group_id,
                        'suggestion' => $item['suggestion'],
                        'reference_image_path' => $item['reference_image_path'],
                    ]);
                }
            });
        }

        session()->forget(['temp_profile_image', 'temp_gift_images']);
        $user->touch(); // Update the updated_at timestamp of the user

        // Notificar al admin sobre la actualización del perfil
        $userName = $user->nickname ?: $user->name;
        // $familyName = $user->familyGroup?->name;
        // $familyText = $familyName ? " ({$familyName})" : '';

        app(ArtisamNotificationService::class)->send(
            title: 'Perfil actualizado',
            message: "{$userName} modificó su perfil.",
            options: [
                'url' => route('admin.users.edit', $user->id),
                'url_title' => 'Ver perfil',
                'category' => 'profile_update',
            ]
        );

        return redirect()->route('user.profile')->with('success', 'Tu perfil se actualizó con éxito.');
    }

    public function profile(Request $request)
    {
        session()->forget(['temp_profile_image', 'temp_gift_images']);

        $user = Auth::user();

        // Asegurar que el usuario esté en family_group_user para su family_group_id actual
        if ($user->family_group_id) {
            $user->familyGroups()->syncWithoutDetaching([$user->family_group_id]);
        }

        $userFamilies = $user->familyGroups()->orderBy('year', 'desc')->get();

        $selectedFamilyId = $request->get('family_group_id');
        $familyGroup = null;

        if ($selectedFamilyId && $userFamilies->contains('id', (int) $selectedFamilyId)) {
            $familyGroup = $userFamilies->firstWhere('id', (int) $selectedFamilyId);
        } else {
            $familyGroup = $user->familyGroup;
            if (!$familyGroup && $userFamilies->isNotEmpty()) {
                $familyGroup = $userFamilies->first();
                $user->update(['family_group_id' => $familyGroup->id]);
            }
        }

        if ($familyGroup) {
            $familyGroup->load(['users' => fn($q) => $q->orderBy('name', 'asc')]);
        }

        // Cargar sugerencias del usuario estrictamente para esta familia seleccionada
        $userGiftSuggestions = GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $familyGroup ? $familyGroup->id : null)
            ->get();
        $user->setRelation('giftSuggestions', $userGiftSuggestions);

        // Usar fechas de la familia
        $now = new \DateTime();
        $isRevealed = $familyGroup ? $familyGroup->isRevealed() : false;
        // Solo se puede editar si es la familia activa del usuario y la fecha límite no ha vencido
        $canEditProfile = $familyGroup ? ($familyGroup->canEditProfile() && $familyGroup->id === $user->family_group_id) : true;
        $isPastYear = $familyGroup && $familyGroup->year < (int) date('Y');

        $secretSanta = null;
        if ($isRevealed && $familyGroup) {
            $assignment = SecretSantaAssignment::where('giver_id', $user->id)
                ->where('family_group_id', $familyGroup->id)
                ->with('receiver')
                ->first();
            
            if ($assignment) {
                $secretSanta = $assignment->receiver;
                
                // Cargar sugerencias del amigo secreto estrictamente para esta familia (sin mezclar años pasados)
                $secretSantaSuggestions = GiftSuggestion::where('user_id', $secretSanta->id)
                    ->where('family_group_id', $familyGroup->id)
                    ->get();

                foreach ($secretSantaSuggestions as $suggestion) {
                    $suggestion->formatted_suggestion = $this->formatSuggestion($suggestion->suggestion);
                }
                $secretSanta->setRelation('giftSuggestions', $secretSantaSuggestions);
            }
        }

        $revealDateJs = $familyGroup && $familyGroup->reveal_date ? $familyGroup->reveal_date->format('Y-m-d\TH:i:s') : null;
        $profileEditEndDate = $familyGroup ? $familyGroup->profile_edit_end_date : null;

        return view('user.profile', compact(
            'user',
            'secretSanta',
            'isRevealed',
            'revealDateJs',
            'canEditProfile',
            'profileEditEndDate',
            'familyGroup',
            'isPastYear',
            'userFamilies'
        ));
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

            // Delete old profile photo if exists
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Update user's profile_photo_path
            $user->update(['profile_photo_path' => $filename]);
        } catch (\Exception $e) {
            // If upload fails, leave profile_photo_path as null
            // You can log the error if needed
        }
    }

    private function formatSuggestionsSnapshot($suggestions): array
    {
        $snapshot = [];

        foreach ($suggestions as $item) {
            $snapshot[] = [
                'suggestion' => trim((string) $item->suggestion),
                'reference_image_path' => $item->reference_image_path ?: null,
            ];
        }

        return $snapshot;
    }

    private function saveGiftImage(User $user, int $index, $imageFile, ?string $oldPath = null): ?string
    {
        try {
            // Generate a unique filename (always .jpg)
            $filename = 'gift-suggestions/' . $user->id . '_' . $index . '_' . Str::random(10) . '.jpg';

            // Resize image to 600px width maintaining aspect ratio
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageFile->getRealPath());
            $image->scale(width: 600);

            // Always save as JPEG to avoid WebP encoder issues
            Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));

            return $filename;
        } catch (\Exception $e) {
            return $oldPath;
        }
    }
}
