<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourPackage;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $packages = TourPackage::paginate($limit);
        return response()->json($packages);
    }

    public function show(Request $request, $id)
    {
        $package = TourPackage::find($id);
        if (!$package) {
            return response()->json([
                'message' => 'Paket wisata tidak ditemukan',
            ], 404);
        }

        return response()->json($package);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validatePackage($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/package',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            TourPackage::create([
                'title' => $data['title'],
                'price' => $data['price'],
                'benefit' => $data['benefit'],
                'thumbnail' => $data['image_url'],
                'public_id' => $data['public_id'],
            ]);

            return response()->json([
                'message' => 'Paket wisata berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat paket wisata',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $package = TourPackage::find($id);
            if (!$package) {
                return response()->json([
                    'message' => 'Paket wisata tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validatePackage($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $package->thumbnail;
            $publicId = $package->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/package',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $package->update([
                'title' => $data['title'],
                'price' => $data['price'],
                'benefit' => $data['benefit'],
                'thumbnail' => $imageUrl,
                'public_id' => $publicId,
            ]);

            return response()->json([
                'message' => 'Paket wisata berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui paket wisata',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $package = TourPackage::find($id);
            if (!$package) {
                return response()->json([
                    'message' => 'Paket wisata tidak ditemukan',
                ], 404);
            }

            if ($package->public_id) {
                Cloudinary::uploadApi()->destroy($package->public_id);
            }

            $package->delete();

            return response()->json([
                'message' => 'Paket wisata berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus paket wisata',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
