<?php

namespace App\Shared\Files;

/**
 * Result of a download feature: a file on a private disk, released only after
 * the feature checked the permission (backend.md 4.5).
 */
final readonly class FileDownload
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $filename,
        public string $mimeType,
    ) {
    }
}
