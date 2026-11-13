<?php

namespace App\Http\Middleware;

use App\Models\FamilyGroup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureFamilyGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Aplicar en rutas de registro customizadas
        if ($request->routeIs('user.register.view') || $request->routeIs('register')) {
            $errorRedirectRoute = \Illuminate\Support\Facades\Auth::check() ? 'user.profile' : 'login';

            if ($request->has('fam')) {
                // Buscar familia por slug
                $familyGroup = FamilyGroup::where('slug', $request->fam)
                                          ->where('is_active', true)
                                          ->first();
                
                if (!$familyGroup) {
                    return redirect()->route($errorRedirectRoute)
                        ->with('error', 'El enlace de registro no es válido. Por favor contacta al administrador.');
                }
                
                // Verificar si la familia ya tiene sorteo realizado
                if ($familyGroup->hasDrawn()) {
                    return redirect()->route($errorRedirectRoute)
                        ->with('error', 'No es posible registrarse porque el sorteo ya fue realizado.');
                }
                
                // Guardar family_group_id en sesión para usarlo en el registro
                session(['registration_family_group_id' => $familyGroup->id]);
            } else {
                // Sin parámetro ?fam= => familia default (id = 1)
                $defaultFamily = FamilyGroup::find(1);
                
                if ($defaultFamily && $defaultFamily->hasDrawn()) {
                    return redirect()->route($errorRedirectRoute)
                        ->with('error', 'No es posible registrarse porque el sorteo ya fue realizado.');
                }
                
                // Establecer familia default en sesión
                session(['registration_family_group_id' => 1]);
            }
        }
        
        return $next($request);
    }
}