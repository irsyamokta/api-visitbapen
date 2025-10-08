<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TourController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $tours = Tour::paginate($limit);
        return response()->json($tours);
    }

    public function show(Request $request, $id)
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return response()->json([
                'message' => 'Wisata tidak ditemukan',
            ], 404);
        }

        return response()->json($tour);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validateTour($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/tour',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            Tour::create([
                'title' => $data['title'],
                'about' => $data['about'],
                'operational' => $data['operational'],
                'location' => $data['location'],
                'start' => $data['start'],
                'end' => $data['end'],
                'facility' => $data['facility'],
                'maps' => $data['maps'],
                'price' => intval($data['price']),
                'thumbnail' => $data['image_url'],
                'public_id' => $data['public_id']
            ]);

            return response()->json([
                'message' => 'Wisata berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat wisata',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tour = Tour::find($id);
            if (!$tour) {
                return response()->json([
                    'message' => 'Wisata tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateTour($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $tour->thumbnail;
            $publicId = $tour->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }

                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/tour',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $tour->update([
                'title' => $data['title'],
                'about' => $data['about'],
                'operational' => $data['operational'],
                'location' => $data['location'],
                'start' => $data['start'],
                'end' => $data['end'],
                'facility' => $data['facility'],
                'maps' => $data['maps'],
                'price' => intval($data['price']),
                'thumbnail' => $imageUrl,
                'public_id' => $publicId
            ]);

            return response()->json([
                'message' => 'Wisata berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui wisata',
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $tour = Tour::find($id);
            if (!$tour) {
                return response()->json([
                    'message' => 'Wisata tidak ditemukan',
                ], 404);
            }

            if ($tour->public_id) {
                Cloudinary::uploadApi()->destroy($tour->public_id);
            }

            $tour->delete();

            return response()->json([
                'message' => 'Wisata berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Wisata gagal dihapus',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
