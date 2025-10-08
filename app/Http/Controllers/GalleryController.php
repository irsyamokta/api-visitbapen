<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $galleries = Gallery::paginate($limit);
        return response()->json($galleries);
    }

    public function show(Request $request, $id)
    {
        $gallery = Gallery::find($id);
        if (!$gallery) {
            return response()->json([
                'message' => 'Galeri tidak ditemukan',
            ], 404);
        }

        return response()->json($gallery);
    }

    public function store (Request $request)
    {
        try {
            $validator = ValidationHelper::validateGallery($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/gallery',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            Gallery::create([
                'title' => $data['title'],
                'caption' => $data['caption'],
                'image' => $data['image_url'],
                'public_id' => $data['public_id'],
            ]);

            return response()->json([
                'message' => 'Galeri berhasil dibuat',
            ], 200);
        } catch ( \Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat galeri',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update (Request $request, $id){
        try {
            $gallery = Gallery::find($id);
            if (!$gallery) {
                return response()->json([
                    'message' => 'Galeri tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateGallery($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $gallery->image;
            $publicId = $gallery->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/gallery',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $gallery->update([
                'title' => $data['title'],
                'caption' => $data['caption'],
                'image' => $imageUrl,
                'public_id' => $publicId,
            ]);

            return response()->json([
                'message' => 'Galeri berhasil diperbarui',
            ], 200);
        } catch ( \Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui galeri',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy (Request $request, $id){
        try {
            $gallery = Gallery::find($id);
            if (!$gallery) {
                return response()->json([
                    'message' => 'Galeri tidak ditemukan',
                ], 404);
            }

            if ($gallery->public_id) {
                Cloudinary::uploadApi()->destroy($gallery->public_id);
            }

            $gallery->delete();

            return response()->json([
                'message' => 'Galeri berhasil dihapus',
            ], 200);
        } catch ( \Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus galeri',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
