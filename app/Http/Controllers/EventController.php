<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $events = Event::paginate($limit);
        return response()->json($events);
    }

    public function show(Request $request, $id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json([
                'message' => 'Agenda tidak ditemukan',
            ], 404);
        }

        return response()->json($event);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validateEvent($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/event',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            Event::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'time' => $data['time'],
                'place' => $data['place'],
                'price' => $data['price'],
                'thumbnail' => $data['image_url'],
                'public_id' => $data['public_id'],
            ]);

            return response()->json([
                'message' => 'Agenda berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat agenda',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::find($id);
            if (!$event) {
                return response()->json([
                    'message' => 'Agenda tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateEvent($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $event->thumbnail;
            $publicId = $event->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/event',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $event->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'date' => $data['date'],
                'time' => $data['time'],
                'place' => $data['place'],
                'price' => $data['price'],
                'thumbnail' => $imageUrl,
                'public_id' => $publicId,
            ]);

            return response()->json([
                'message' => 'Agenda berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui agenda',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json([
                    'message' => 'Agenda tidak ditemukan',
                ], 404);
            }

            if ($event->public_id) {
                Cloudinary::uploadApi()->destroy($event->public_id);
            }

            $event->delete();

            return response()->json([
                'message' => 'Agenda berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus agenda',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
