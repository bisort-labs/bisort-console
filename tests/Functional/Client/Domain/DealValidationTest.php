<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Domain;

use App\Client\Domain\Deal;
use App\Client\Domain\Enum\DealStage;
use App\Client\Domain\Enum\LeadSource;
use App\Client\Domain\Lead;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DealValidationTest extends KernelTestCase
{
    /**
     * @dataProvider requiredFieldProvider
     */
    public function test_it_validates_required_fields(string $propertyPath): void
    {
        $violations = $this->getValidator()->validate(new Deal());

        self::assertGreaterThan(0, $violations->count());
        self::assertContains($propertyPath, $this->getViolationPropertyPaths($violations));
    }

    public function test_it_accepts_enum_backed_stage_value(): void
    {
        $deal = new Deal()
            ->setLead($this->createLead())
            ->setTitle('Implementation Deal')
            ->setCurrency('EUR')
            ->setExpectedValueCents(150000)
            ->setStage(DealStage::ProposalSent);

        self::assertCount(0, $this->getValidator()->validate($deal));
        self::assertSame(DealStage::ProposalSent, $deal->getStage());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function requiredFieldProvider(): iterable
    {
        yield 'lead' => ['lead'];
        yield 'title' => ['title'];
        yield 'currency' => ['currency'];
        yield 'expected value' => ['expectedValueCents'];
        yield 'stage' => ['stage'];
    }

    private function createLead(): Lead
    {
        return new Lead()
            ->setName('Ada Lovelace')
            ->setEmail('ada@example.com')
            ->setSource(LeadSource::Website);
    }

    private function getValidator(): ValidatorInterface
    {
        return self::getContainer()->get(ValidatorInterface::class);
    }

    /**
     * @return list<string>
     */
    private function getViolationPropertyPaths(iterable $violations): array
    {
        $propertyPaths = [];

        foreach ($violations as $violation) {
            $propertyPaths[] = $violation->getPropertyPath();
        }

        return $propertyPaths;
    }
}
