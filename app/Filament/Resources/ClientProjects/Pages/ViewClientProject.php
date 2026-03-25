<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Pages;

use App\Filament\Resources\ClientProjects\ClientProjectResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

class ViewClientProject extends ViewRecord
{
    protected static string $resource = ClientProjectResource::class;

    /**
     * @return list<Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
