<?php

declare(strict_types=1);

namespace App\Filament\Resources\ClientProjects\Pages;

use App\Filament\Resources\ClientProjects\ClientProjectResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListClientProjects extends ListRecords
{
    protected static string $resource = ClientProjectResource::class;

    /**
     * @return list<Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
