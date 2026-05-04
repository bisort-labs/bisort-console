<?php

declare(strict_types=1);

namespace App\Client\Presentation;

use App\Client\Domain\Enum\LeadSource;
use App\Client\Domain\Lead;
use App\Shared\Presentation\AbstractResourceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractResourceCrudController<Lead>
 */
class LeadCrudController extends AbstractResourceCrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
        parent::__construct($this->requestStack);
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Lead::class;
    }

    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield EmailField::new('email');
        yield TextField::new('company');
        yield TelephoneField::new('phone');

        yield ChoiceField::new('source')
            ->setChoices($this->getSourceChoices());

        yield AssociationField::new('owner')
            ->setFormTypeOption('choice_label', 'username');

        yield BooleanField::new('isActive')
            ->renderAsSwitch();

        yield TextField::new('street')->hideOnIndex();
        yield TextField::new('zipCode')->hideOnIndex();
        yield TextField::new('city')->hideOnIndex();
        yield TextField::new('state')->hideOnIndex();
        yield TextField::new('country')->hideOnIndex();
    }

    /**
     * @return array<string, LeadSource>
     */
    private function getSourceChoices(): array
    {
        return [
            'Website' => LeadSource::Website,
            'Referral' => LeadSource::Referral,
            'Cold Outreach' => LeadSource::ColdOutreach,
            'LinkedIn' => LeadSource::LinkedIn,
            'Facebook' => LeadSource::Facebook,
            'Google Plus' => LeadSource::GooglePlus,
            'Xing' => LeadSource::Xing,
            'YouTube' => LeadSource::YouTube,
            'Instagram' => LeadSource::Instagram,
            'X.com' => LeadSource::XCom,
            'Other' => LeadSource::Other,
        ];
    }
}
