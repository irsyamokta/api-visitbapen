<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $tickets = Ticket::paginate($limit);
        return response()->json($tickets);
    }

    public function show(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json([
                'message' => 'Tiket tidak ditemukan',
            ], 404);
        }

        return response()->json($ticket);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validateTicket($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/ticket',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            Ticket::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'price' => $data['price'],
                'cover' => $data['image_url'],
                'public_id' => $data['public_id'],
                'created_by' => auth()->user()->id
            ]);

            return response()->json([
                'message' => 'Tiket berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat tiket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateTicket($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $ticket->cover;
            $publicId = $ticket->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }

                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/ticket',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $ticket->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'location' => $data['location'],
                'price' => $data['price'],
                'cover' => $imageUrl,
                'public_id' => $publicId,
            ]);

            return response()->json([
                'message' => 'Tiket berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui tiket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json([
                    'message' => 'Tiket tidak ditemukan',
                ], 404);
            }

            if ($ticket->public_id) {
                Cloudinary::uploadApi()->destroy($ticket->public_id);
            }

            $ticket->delete();

            return response()->json([
                'message' => 'Tiket berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus tiket',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
