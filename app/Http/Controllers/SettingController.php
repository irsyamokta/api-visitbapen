<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SelectOption;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SelectOption::all();
        return response()->json($settings);
    }

    public function show(Request $request, $id)
    {
        $setting = SelectOption::find($id);
        if (!$setting) {
            return response()->json([
                'message' => 'Setting tidak ditemukan',
            ], 404);
        }

        return response()->json($setting);
    }

    public function store (Request $request)
    {
        try {
            $validator = ValidationHelper::validateSetting($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            SelectOption::create([
                'name' => $data['name'],
                'category' => $data['category'],
                'type' => $data['type'] ?? null,
            ]);

            return response()->json([
                'message' => 'Setting berhasil dibuat'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update (Request $request, $id)
    {
        try {
            $setting = SelectOption::find($id);
            if (!$setting) {
                return response()->json([
                    'message' => 'Setting tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateSetting($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $setting->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'type' => $data['type'] ?? null,
            ]);

            return response()->json([
                'message' => 'Setting berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy (Request $request, $id)
    {
        try {
            $setting = SelectOption::find($id);
            if (!$setting) {
                return response()->json([
                    'message' => 'Setting tidak ditemukan',
                ], 404);
            }

            $setting->delete();

            return response()->json([
                'message' => 'Setting berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
