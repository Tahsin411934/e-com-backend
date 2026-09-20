<?php

namespace Modules\Identity\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = $request->user()->customerProfile()->firstOrCreate([]);

        return ApiResponse::success($profile, 'Customer profile retrieved successfully.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'avatar' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:30'],
            'marketing_consent' => ['sometimes', 'boolean'],
        ]);

        $profile = $request->user()->customerProfile()->firstOrCreate([]);
        $profile->update($data);

        return ApiResponse::success($profile->fresh(), 'Customer profile updated successfully.');
    }
}
