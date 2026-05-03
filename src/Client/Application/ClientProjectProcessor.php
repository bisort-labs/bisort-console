<?php

declare(strict_types=1);

namespace App\Client\Application;

use App\Client\Domain\ClientProject;
use App\Shared\Application\ResourceProcessorInterface;
use App\Shared\Domain\AbstractResource;
use Override;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @implements ResourceProcessorInterface<ClientProject>
 */
readonly class ClientProjectProcessor implements ResourceProcessorInterface
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    #[Override]
    public function supports(AbstractResource $resource): bool
    {
        return $resource instanceof ClientProject;
    }

    #[Override]
    public function process(object $entity): ClientProject
    {
        $this->generateSlug($entity);

        return $entity;
    }

    private function generateSlug(ClientProject $clientProject): void
    {
        $title = $clientProject->getTitle();

        if ($title === null || trim($title) === '') {
            return;
        }

        $title = mb_strtolower($title);

        $clientProject->setSlug(
            $this->slugger->slug($title)->toString(),
        );
    }
}
