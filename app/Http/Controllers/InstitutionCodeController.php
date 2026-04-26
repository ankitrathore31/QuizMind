<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstitutionCodeController extends Controller
{
    
    public function validate(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->input('code', '')));

        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'Please enter a code.']);
        }

        $institution = Institution::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$institution) {
            return response()->json(['valid' => false, 'message' => 'Institution code not found.']);
        }

        return response()->json([
            'valid'            => true,
            'institution_name' => $institution->name,
            'city'             => $institution->city,
            'type'             => $institution->type,
        ]);
    }

    // POST: authenticated student joins institution from their dashboard
    public function join(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $code = strtoupper(trim($request->input('code')));

        // Already linked to this code
        if ($user->ref_code === $code) {
            return response()->json([
                'success' => false,
                'message' => 'You are already linked to this institution.',
            ]);
        }

        $institution = Institution::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$institution) {
            return response()->json([
                'success' => false,
                'message' => 'Institution code not found or inactive.',
            ]);
        }

        $user->update(['ref_code' => $code]);

        return response()->json([
            'success'          => true,
            'institution_name' => $institution->name,
            'message'          => "You have joined {$institution->name}!",
        ]);
    }

}