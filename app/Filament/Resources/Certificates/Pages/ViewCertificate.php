<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * Read-only detail – issued certificates are immutable (no edit / delete actions).
 */
class ViewCertificate extends ViewRecord
{
    protected static string $resource = CertificateResource::class;
}
