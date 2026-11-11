<?php

namespace App\Http\Controllers;

use App\Shared\Files\FileContent;
use App\Shared\Files\FileDownload;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controllers only translate HTTP to Actions and back (BR6): no business logic here.
 */
abstract class Controller
{
    protected function sendFile(FileDownload $file): Response
    {
        return Storage::disk($file->disk)->download($file->path, $file->filename, [
            'Content-Type' => $file->mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function sendContent(FileContent $file): Response
    {
        return response($file->content, 200, [
            'Content-Type' => $file->mimeType,
            'Content-Disposition' => 'attachment; filename="'.$file->filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
