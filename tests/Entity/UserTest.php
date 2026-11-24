<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class UserTest extends TestCase
{
    public function testNameIsRequired(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $this->assertCount(1, $validator->validateProperty(new User(), 'name'));
        $this->assertCount(0, $validator->validateProperty(new User()->setName('Ada Lovelace'), 'name'));
    }
}
