<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemporaryMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    /**
     * Store temporary media from AJAX upload.
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            return response()->json(['error' => 'Tidak ada file yang diunggah'], 422);
        }

        // Tentukan batas ukuran (KB) secara dinamis
        $mime = $file->getMimeType();
        $limit = 2048; // Default 2MB (Gamber)
        $typeLabel = 'Gambar';

        if (str_contains($mime, 'pdf') || str_contains($mime, 'word') || str_contains($mime, 'officedocument')) {
            $limit = 10240; // 10MB (Dokumen)
            $typeLabel = 'Dokumen';
        } elseif (str_contains($mime, 'video/')) {
            $limit = 51200; // 50MB (Video)
            $typeLabel = 'Video';
        }

        $validator = Validator::make($request->all(), [
            'file' => "required|file|max:$limit",
            'folder' => 'nullable|string'
        ], [
            'file.max' => "Ukuran $typeLabel terlalu besar! Maksimal " . ($limit / 1024) . "MB."
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            // Create a temporary record to hold the media
            $temp = TemporaryMedia::create([
                'user_id' => auth()->id(),
                'folder' => $request->folder ?? 'general'
            ]);

            // Attach the file using Spatie MediaLibrary
            $media = $temp->addMediaFromRequest('file')
                ->toMediaCollection('temp');

            return response()->json([
                'success' => true,
                'media_id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->file_name
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengunggah file: ' . $e->getMessage()], 500);
        }
    }
}
