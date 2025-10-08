<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $users = User::paginate($limit);
        return response()->json($users);
    }

    public function contact()
    {
        $user = User::where('role', 'admin')->select('name', 'email', 'phone', 'instagram')->get();
        return response()->json($user);
    }

    public function show(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validateUser($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'message' => 'Email sudah digunakan',
                ], 422);
            }

            if (User::where('phone', $request->phone)->exists()) {
                return response()->json([
                    'message' => 'Nomor telepon sudah digunakan',
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/user',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'instagram' => $data['instagram'] ?? null,
                'password' => bcrypt($data['password']),
                'avatar' => $data['image_url'],
                'public_id' => $data['public_id']
            ]);

            return response()->json([
                'message' => 'User berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateUser($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                if ($user->public_id) {
                    Cloudinary::uploadApi()->destroy($user->public_id);
                }

                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/profile',
                ]);

                $data['avatar'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                if (User::where('email', $data['email'])->exists()) {
                    return response()->json(['message' => 'Email sudah digunakan'], 400);
                }

                $data['is_verified'] = false;
                $data['verification_token'] = Str::random(64);
            }

            if (isset($data['phone']) && $data['phone'] !== $user->phone) {
                if (User::where('phone', $data['phone'])->exists()) {
                    return response()->json(['message' => 'Nomor telepon sudah digunakan'], 400);
                }
            }

            $user->update($data);

            return response()->json([
                'message' => 'User berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateById(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateUser($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $user->avatar;
            $publicId = $user->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/profile',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                if (User::where('email', $data['email'])->exists()) {
                    return response()->json(['message' => 'Email sudah digunakan'], 400);
                }

                $data['is_verified'] = false;
                $data['verification_token'] = Str::random(64);
            }

            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'instagram' => $data['instagram'] ?? null,
                'password' => isset($data['password']) && $data['password']
                    ? bcrypt($data['password'])
                    : $user->password,
                'avatar' => $imageUrl,
                'public_id' => $publicId
            ]);

            return response()->json([
                'message' => 'User berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                ], 404);
            }

            if ($user->public_id) {
                Cloudinary::uploadApi()->destroy($user->public_id);
            }

            $user->delete();

            return response()->json([
                'message' => 'User berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
