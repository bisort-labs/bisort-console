<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\AbstractResource;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T of AbstractResource
 */
#[AutoconfigureTag('shared.application.resource_processor')]
interface ResourceProcessorInterface
{
    public function supports(AbstractResource $resource): bool;

    /**
     * @param T $entity
     *
     * @return T
     */
    public function process(object $entity): AbstractResource;
}
