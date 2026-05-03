<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\AbstractResource;

/**
 * @template T of AbstractResource
 */
interface ResourceProcessorInterface
{
    /**
     * @param T $entity
     *
     * @return T
     */
    public function process(object $entity): AbstractResource;
}
