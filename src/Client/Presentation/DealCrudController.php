<?php

declare(strict_types=1);

namespace App\Client\Presentation;

use App\Client\Domain\Deal;
use App\Client\Domain\Enum\DealStage;
use App\Shared\Presentation\AbstractResourceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<Deal>
 */
class DealCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($this->requestStack);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Deal::class;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');

        yield AssociationField::new('lead')
            ->setFormTypeOption('choice_label', 'name');

        yield AssociationField::new('project')
            ->setFormTypeOption('choice_label', 'title');

        yield MoneyField::new('expectedValueCents', 'Expected Value')
            ->setStoredAsCents()
            ->setCurrencyPropertyPath('currency');

        yield CurrencyField::new('currency')
            ->showCode();

        yield PercentField::new('probability')
            ->setStoredAsFractional(false);

        yield ChoiceField::new('stage')
            ->setChoices($this->getStageChoices());

        yield DateField::new('closeDate');

        yield AssociationField::new('owner')
            ->setFormTypeOption('choice_label', 'username');

        yield BooleanField::new('isActive')
            ->renderAsSwitch();

        yield TextField::new('lostReason')->hideOnIndex();
        yield TextareaField::new('notes')->hideOnIndex();
    }

    /**
     * @return array<string, DealStage>
     */
    private function getStageChoices(): array
    {
        return [
            'New' => DealStage::New,
            'Contacted' => DealStage::Contacted,
            'Proposal Sent' => DealStage::ProposalSent,
            'Negotiation' => DealStage::Negotiation,
            'Won' => DealStage::Won,
            'Lost' => DealStage::Lost,
        ];
    }
}
