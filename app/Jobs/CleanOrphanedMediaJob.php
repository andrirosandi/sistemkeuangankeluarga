<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CleanOrphanedMediaJob implements ShouldQueue
{
    use Queueable;

    public $mediaId;

    /**
     * Create a new job instance.
     */
    public function __construct($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $media = Media::find($this->mediaId);

        // Jika media tidak ditemukan, berarti sudah terhapus, abaikan.
        if (!$media) {
            return;
        }

        // Cek apakah media masih menggantung di TemporaryUpload
        // Atau jika model_type masih berisi sesuatu yang menandakan status sementara
        if ($media->model_type === 'App\Models\TemporaryUpload') {
            $media->delete(); 
        }
    }
}
