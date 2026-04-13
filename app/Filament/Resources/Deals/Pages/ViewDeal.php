<?php

declare(strict_types=1);

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\Pages\Concerns\InteractsWithActionLogs;
use App\Models\Deal;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

/**
 * @extends ViewRecord<Deal>
 */
class ViewDeal extends ViewRecord
{
    use InteractsWithActionLogs;

    protected static string $resource = DealResource::class;

    /**
     * @return array<Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->getAddNoteAction(),
        ];
    }
}
