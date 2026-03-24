<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Pages;

use App\Filament\Resources\ClientProjects\ClientProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientProject extends CreateRecord
{
    protected static string $resource = ClientProjectResource::class;
}
