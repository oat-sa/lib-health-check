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

use OAT\Library\HealthCheck\Result\CheckerResult;
use PHPUnit\Framework\TestCase;

class CheckerResultTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $subject = new CheckerResult();

        $this->assertTrue($subject->isSuccess());
        $this->assertNull($subject->getMessage());
    }

    public function testItCanSetAndGetSuccess(): void
    {
        $subject = new CheckerResult();

        $subject->setSuccess(false);

        $this->assertFalse($subject->isSuccess());
    }

    public function testItCanSetAndGetMessage(): void
    {
        $subject = new CheckerResult();

        $subject->setMessage('test');

        $this->assertEquals('test', $subject->getMessage());
    }
}
