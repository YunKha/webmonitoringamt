<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    /**
     * GET /api/profile
     * Return data profil sopir yang sedang login
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data'    => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'role'             => $user->role,
                'phone'            => $user->phone,
                'employee_id'      => $user->employee_id,
                'total_deliveries' => $user->total_deliveries, // dari accessor
            ],
        ]);
    }

    /**
     * PUT /api/profile
     * Update data profil sopir
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'phone'       => 'sometimes|nullable|string|max:20',
            'employee_id' => 'sometimes|nullable|string|max:50',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'employee_id']));

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data'    => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'role'             => $user->role,
                'phone'            => $user->phone,
                'employee_id'      => $user->employee_id,
                'total_deliveries' => $user->total_deliveries,
            ],
        ]);
    }
}