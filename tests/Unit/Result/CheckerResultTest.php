<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\HealthCheck\Tests\Unit\Result;

use Exception;
use OAT\Library\HealthCheck\Result\CheckerResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class CheckerResultTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $subject = new CheckerResult();

        $this->assertTrue($subject->isSuccess());
        $this->assertNull($subject->getMessage());
        $this->assertEmpty($subject->getContext());
    }

    public function testItCanSetAndGetSuccess(): void
    {
        $subject = new CheckerResult();

        $subject->setSuccess(false);

        $this->assertFalse($subject->isSuccess());
    }

    public function testItCanSetAndGetMessage(): void
    {
        $message = 'test';
        $subject = new CheckerResult();

        $subject->setMessage($message);

        $this->assertEquals($message, $subject->getMessage());
    }

    public function testItCanSetAndGetContext(): void
    {
        $context = ['test'];
        $subject = new CheckerResult();

        $subject->setContext($context);

        $this->assertEquals($context, $subject->getContext());
    }

    public function testItCanSetContextFromExceptionAndGetContext(): void
    {
        $originalException = new Exception('original', 0);
        $exception = new Exception('test', 0, $originalException);
        $flattenedException = FlattenException::createFromThrowable($exception);

        $expectedContext = [
            'class' => $flattenedException->getClass(),
            'file'  => $flattenedException->getFile(),
            'line'  => $flattenedException->getLine(),
            'trace' => $flattenedException->getTrace(),
        ];

        $subject = new CheckerResult();

        $subject->setContextFromThrowable($exception);

        $this->assertEquals($expectedContext, $subject->getContext());
    }
}
