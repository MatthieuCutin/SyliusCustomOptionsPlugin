<?php
declare(strict_types=1);

namespace Tests\Brille24\CustomerOptionsPlugin\Validator;

use Brille24\CustomerOptionsPlugin\Entity\CustomerOptions\{
    CustomerOptionAssociationInterface, CustomerOptionInterface
};
use Brille24\CustomerOptionsPlugin\Validator\Constraints\CustomerOptionAssociationConstraintValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerOptionAssociationConstraintValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerOptionAssociationConstraintValidator */
    private $customerOptionAssociationConstraintValidator;

    /** @var array */
    private $violations = [];

    /** @var CustomerOptionInterface[] */
    private $customerOptions = [];

    //<editor-fold desc="Setup" default="collapsed">
    protected function setUp()
    {
        $this->customerOptionAssociationConstraintValidator = new CustomerOptionAssociationConstraintValidator();

        $context = self::createMock(ExecutionContextInterface::class);
        $context->method('addViolation')->willReturnCallback(function (?string $message): void {
            var_dump($message);
            $this->violations[] = $message;
        });
        $this->customerOptionAssociationConstraintValidator->initialize($context);
    }

    private function createCustomerOptionAssociation(string $customerOptionCode): CustomerOptionAssociationInterface
    {
        if (isset($this->customerOptions[$customerOptionCode])) {
            $customerOption = $this->customerOptions[$customerOptionCode];
        } else {
            $customerOption = self::createMock(CustomerOptionInterface::class);
            $customerOption->method('getCode')->willReturn($customerOptionCode);
            $this->customerOptions[$customerOptionCode] = $customerOption;
        }

        $customerOptionAssociation = self::createMock(CustomerOptionAssociationInterface::class);
        $customerOptionAssociation->method('getOption')->willReturn($customerOption);

        return $customerOptionAssociation;
    }

    //</editor-fold>

    public function testWrongElementType(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage(CustomerOptionAssociationConstraintValidator::class . ' can only validate collections containing ' . CustomerOptionAssociationInterface::class);

        $constraint = self::createMock(Constraint::class);
        $this->customerOptionAssociationConstraintValidator->validate(1, $constraint);
    }

    public function testWrongElementTypeInList(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid entry type');

        $constraint = self::createMock(Constraint::class);
        $this->customerOptionAssociationConstraintValidator->validate(new ArrayCollection([1]), $constraint);
    }

    public function testValidate(): void
    {
        $collection = new ArrayCollection([]);
        $constraint = self::createMock(Constraint::class);

        $this->customerOptionAssociationConstraintValidator->validate($collection, $constraint);

        self::assertEquals(0, count($this->violations));
    }

    public function testValidateWithDuplicate(): void
    {
        $collection = new ArrayCollection(
            [
                $this->createCustomerOptionAssociation('customerOption1'),
                $this->createCustomerOptionAssociation('customerOption1')
            ]);
        $constraint = self::createMock(Constraint::class);

        $this->customerOptionAssociationConstraintValidator->validate($collection, $constraint);

        self::assertEquals(1, count($this->violations));
    }

    public function testValidValidate(): void
    {
        $collection = new ArrayCollection(
            [
                $this->createCustomerOptionAssociation('customerOption1'),
                $this->createCustomerOptionAssociation('customerOption2')
            ]);
        $constraint = self::createMock(Constraint::class);

        $this->customerOptionAssociationConstraintValidator->validate($collection, $constraint);

        self::assertEquals(0, count($this->violations));
    }
}
