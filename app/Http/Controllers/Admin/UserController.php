<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentYear = (int) date('Y');
        $selectedYear = $request->get('year', $currentYear);

        if (is_numeric($selectedYear)) {
            $selectedYear = (int) $selectedYear;
        }

        // Obtener todos los años disponibles en la BD
        $yearExpression = DB::getDriverName() === 'sqlite' ? "strftime('%Y', created_at)" : 'YEAR(created_at)';
        $userYears = User::selectRaw("DISTINCT {$yearExpression} as year")
            ->whereNotNull('created_at')
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->filter()
            ->toArray();
        $familyYears = FamilyGroup::select('year')->distinct()->whereNotNull('year')->pluck('year')->toArray();
        $availableYears = array_values(array_unique(array_merge([$currentYear], $familyYears, $userYears)));
        rsort($availableYears);

        // Obtener familias para el filtro según el año seleccionado
        $familyGroupsQuery = FamilyGroup::query();
        if ($selectedYear !== 'all') {
            $familyGroupsQuery->where('year', $selectedYear);
        }
        $familyGroups = $familyGroupsQuery->withCount('users')->orderBy('name', 'asc')->get();

        // Validar si el family_group_id seleccionado pertenece al año seleccionado
        $familyGroupId = $request->get('family_group_id');
        if ($familyGroupId && $selectedYear !== 'all') {
            if (!$familyGroups->contains('id', (int) $familyGroupId)) {
                $familyGroupId = null;
            }
        }

        $query = User::with(['secretSantaAssignment.receiver', 'familyGroup']);

        // Filtrar por año
        if ($selectedYear !== 'all') {
            $query->where(function ($q) use ($selectedYear) {
                $q->whereHas('familyGroup', function ($fq) use ($selectedYear) {
                    $fq->where('year', $selectedYear);
                })->orWhere(function ($oq) use ($selectedYear) {
                    $oq->whereNull('family_group_id')
                       ->whereYear('created_at', $selectedYear);
                })->orWhereHas('secretSantaAssignments', function ($aq) use ($selectedYear) {
                    $aq->whereHas('familyGroup', function ($afq) use ($selectedYear) {
                        $afq->where('year', $selectedYear);
                    });
                });
            });
        }

        // Filtrar por familia si se especifica
        if (!empty($familyGroupId)) {
            $query->where('family_group_id', $familyGroupId);
        }

        $users = $query->orderBy('updated_at', 'desc')->get();

        return view('admin.users.index', compact('users', 'familyGroups', 'availableYears', 'selectedYear', 'familyGroupId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentYear = (int) date('Y');
        $familyGroups = FamilyGroup::where('is_active', true)
            ->orderBy('year', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $existingUsers = User::select('id', 'name', 'dni', 'phone', 'email', 'profile_photo_path', 'funny_profile_photo_path', 'family_group_id', 'nickname')
            ->with('familyGroup:id,name,year')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.users.create', compact('familyGroups', 'existingUsers', 'currentYear'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $identifier = trim($request->dni);
        $user = User::where('phone', $identifier)->orWhere('dni', $identifier)->first();

        $request->validate([
            'name' => 'required|string|min:2|max:255',
            'dni' => 'required|string|regex:/^\d{8,9}$/',
            'family_group_id' => 'required|exists:family_groups,id',
            'email' => 'nullable|email|max:255',
            'nickname' => 'nullable|string|max:255',
            'password' => $user ? 'nullable|string|min:4' : 'required|string|min:4',
            'profile_photo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'dni.required' => 'El Celular o DNI es obligatorio.',
            'dni.regex' => 'Debe contener 9 dígitos (celular) u 8 dígitos (DNI).',
            'family_group_id.required' => 'Debes seleccionar una familia.',
            'family_group_id.exists' => 'La familia seleccionada no es válida.',
            'password.required' => 'La contraseña es obligatoria para un usuario nuevo.',
            'password.min' => 'La contraseña debe tener al menos 4 caracteres.',
        ]);

        $targetFamily = FamilyGroup::findOrFail($request->family_group_id);

        if ($targetFamily->hasDrawn()) {
            return back()->withInput()->with('error', 'No se pueden agregar participantes a una familia cuyo sorteo ya ha sido realizado.');
        }

        $isPhone = strlen($identifier) === 9;

        if ($user) {
            // Usuario ya existe en la base de datos
            if ($user->family_group_id) {
                $currentFamily = $user->familyGroup;
                if ($currentFamily && $currentFamily->year == $targetFamily->year && $currentFamily->id != $targetFamily->id) {
                    $userDisplayId = $user->phone ?? $user->dni;
                    return back()->withInput()->with('error', "El usuario {$user->name} ({$userDisplayId}) ya está registrado en la familia '{$currentFamily->name}' para el año {$targetFamily->year}.");
                }
            }

            // Actualizar datos y reasignar a la familia seleccionada
            $updateData = [
                'name' => $request->name,
                'family_group_id' => $targetFamily->id,
                'family_id' => null,
            ];

            if ($isPhone) {
                $updateData['phone'] = $identifier;
            }

            if ($request->filled('email')) {
                $updateData['email'] = $request->email;
            }
            if ($request->filled('nickname')) {
                $updateData['nickname'] = $request->nickname;
            }
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $targetFamily->users()->syncWithoutDetaching([$user->id]);

            if ($request->hasFile('profile_photo_path')) {
                $this->saveProfileImage($user, $request->file('profile_photo_path'));
            }

            return redirect()->route('admin.users.index', ['year' => $targetFamily->year, 'family_group_id' => $targetFamily->id])
                ->with('updated', "El participante {$user->name} fue asignado exitosamente a la familia {$targetFamily->name}.");
        }

        // Crear nuevo usuario desde cero
        $userData = [
            'name' => $request->name,
            'phone' => $isPhone ? $identifier : null,
            'dni' => !$isPhone ? $identifier : null,
            'email' => $request->email ?? null,
            'nickname' => $request->nickname ?? null,
            'family_group_id' => $targetFamily->id,
            'password' => Hash::make($request->password),
        ];

        $newUser = User::create($userData);
        $targetFamily->users()->syncWithoutDetaching([$newUser->id]);

        if ($request->hasFile('profile_photo_path')) {
            $this->saveProfileImage($newUser, $request->file('profile_photo_path'));
        }

        return redirect()->route('admin.users.index', ['year' => $targetFamily->year, 'family_group_id' => $targetFamily->id])
            ->with('updated', "El usuario {$newUser->name} fue creado y asignado exitosamente a la familia {$targetFamily->name}.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show user profile to admin (read-only)
     */
    public function showProfile(User $user)
    {
        $familyGroup = $user->familyGroup;
        
        // Determine if info is revealed for this user
        $isRevealed = $familyGroup && $familyGroup->isRevealed();
        
        $secretSanta = null;
        if ($isRevealed && $familyGroup) {
            $assignment = \App\Models\SecretSantaAssignment::where('giver_id', $user->id)
                ->where('family_group_id', $familyGroup->id)
                ->with('receiver')
                ->first();
            
            if ($assignment) {
                $secretSanta = $assignment->receiver;
                // Cargar estrictamente las sugerencias de la familia asignada (sin mezclar otros años)
                $secretSantaSuggestions = \App\Models\GiftSuggestion::where('user_id', $secretSanta->id)
                    ->where('family_group_id', $familyGroup->id)
                    ->get();
                $secretSanta->setRelation('giftSuggestions', $secretSantaSuggestions);
            }
        }

        // Cargar sugerencias del usuario para esta familia
        $userGiftSuggestions = \App\Models\GiftSuggestion::where('user_id', $user->id)
            ->where('family_group_id', $familyGroup ? $familyGroup->id : null)
            ->get();
        $user->setRelation('giftSuggestions', $userGiftSuggestions);

        // Format dates for JavaScript (admin doesn't need edit limits)
        $revealDateJs = $familyGroup && $familyGroup->reveal_date ? $familyGroup->reveal_date->format('Y-m-d\TH:i:s') : null;
        $profileEditEndDate = $familyGroup ? $familyGroup->profile_edit_end_date : null;

        // Pass additional flag to indicate admin view
        $isAdminView = true;

        return view('user.profile', compact('user', 'secretSanta', 'isRevealed', 'revealDateJs', 'profileEditEndDate', 'familyGroup', 'isAdminView'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load(['familyGroup', 'allGiftSuggestions.familyGroup']);
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
            'nickname' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('funny_profile_photo_path')) {
            $this->saveFunnyProfileImage($user, $request->file('funny_profile_photo_path'));
        }

        $user->update([
            'nickname' => $request->input('nickname'),
        ]);

        $year = $user->familyGroup ? $user->familyGroup->year : date('Y');

        return redirect()->route('admin.users.index', ['year' => $year])->with('updated', $user->name);
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

    /**
     * Save the standard profile image for the user.
     */
    private function saveProfileImage(User $user, $imageFile): void
    {
        try {
            $filename = 'profile-photos/' . $user->id . '_' . Str::random(10) . '.jpg';
            $manager = new ImageManager(new Driver());
            $image = $manager->read($imageFile->getRealPath());
            $image->scale(width: 600);
            Storage::disk('public')->put($filename, $image->encode(new JpegEncoder(quality: 90)));
            $user->update(['profile_photo_path' => $filename]);
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}
