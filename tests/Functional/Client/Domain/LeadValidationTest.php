<?php

declare(strict_types=1);

namespace App\Tests\Functional\Client\Domain;

use App\Client\Domain\Enum\LeadSource;
use App\Client\Domain\Lead;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class LeadValidationTest extends KernelTestCase
{
    /**
     * @dataProvider requiredFieldProvider
     */
    public function test_it_validates_required_fields(string $propertyPath): void
    {
        $violations = $this->getValidator()->validate(new Lead());

        self::assertGreaterThan(0, $violations->count());
        self::assertContains($propertyPath, $this->getViolationPropertyPaths($violations));
    }

    public function test_it_accepts_enum_backed_source_value(): void
    {
        $lead = new Lead()
            ->setName('Ada Lovelace')
            ->setEmail('ada@example.com')
            ->setSource(LeadSource::Referral);

        self::assertCount(0, $this->getValidator()->validate($lead));
        self::assertSame(LeadSource::Referral, $lead->getSource());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function requiredFieldProvider(): iterable
    {
        yield 'name' => ['name'];
        yield 'email' => ['email'];
        yield 'source' => ['source'];
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
