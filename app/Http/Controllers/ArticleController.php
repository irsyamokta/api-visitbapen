<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Helpers\ValidationHelper;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $articles = Article::paginate($limit);
        return response()->json($articles);
    }

    public function show(Request $request, $id)
    {
        $article = Article::find($id);
        if (!$article) {
            return response()->json([
                'message' => 'Artikel tidak ditemukan',
            ], 404);
        }

        return response()->json($article);
    }

    public function store(Request $request)
    {
        try {
            $validator = ValidationHelper::validateArticle($request->all(), true);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            if ($request->hasFile('file')) {
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/article',
                ]);

                $data['image_url'] = $uploaded['secure_url'];
                $data['public_id'] = $uploaded['public_id'];
            }

            Article::create([
                'editor_id' => auth()->user()->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'writer' => $data['writer'],
                'thumbnail' => $data['image_url'],
                'public_id' => $data['public_id'],
            ]);

            return response()->json([
                'message' => 'Artikel berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $article = Article::find($id);
            if (!$article) {
                return response()->json([
                    'message' => 'Artikel tidak ditemukan',
                ], 404);
            }

            $validator = ValidationHelper::validateArticle($request->all(), false);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $imageUrl = $article->thumbnail;
            $publicId = $article->public_id;

            if ($request->hasFile('file')) {
                if ($publicId) {
                    Cloudinary::uploadApi()->destroy($publicId);
                }
                $uploaded = Cloudinary::uploadApi()->upload($request->file('file')->getRealPath(), [
                    'folder' => 'images/article',
                ]);

                $imageUrl = $uploaded['secure_url'];
                $publicId = $uploaded['public_id'];
            }

            $article->update([
                'editor_id' => auth()->user()->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'writer' => $data['writer'],
                'thumbnail' => $imageUrl,
                'public_id' => $publicId,
            ]);

            return response()->json([
                'message' => 'Artikel berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $article = Article::find($id);

            if (!$article) {
                return response()->json([
                    'message' => 'Artikel tidak ditemukan',
                ], 404);
            }

            if ($article->public_id) {
                Cloudinary::uploadApi()->destroy($article->public_id);
            }

            $article->delete();

            return response()->json([
                'message' => 'Artikel berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus artikel',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
