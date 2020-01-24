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
use OAT\Library\HealthCheck\Result\CheckerResultCollection;
use PHPUnit\Framework\TestCase;

class CheckerResultCollectionTest extends TestCase
{
    public function testItCanBeUsedIteratively(): void
    {
        $subject = new CheckerResultCollection();

        $subject->add('success', new CheckerResult(true, 'success'));

        $this->assertCount(1, $subject);
        $this->assertFalse($subject->hasErrors());


        $subject->add('failure', new CheckerResult(false, 'failure'));

        $this->assertCount(2, $subject);
        $this->assertTrue($subject->hasErrors());

        foreach ($subject as $result) {
            $this->assertInstanceOf(CheckerResult::class, $result);
        }
    }

    public function testItCanBeJsonSerialized(): void
    {
        $subject = new CheckerResultCollection();

        $subject
            ->add('success', new CheckerResult(true, 'success'))
            ->add('failure', new CheckerResult(false, 'failure'));

        $this->assertEquals(
            [
                'success' => false,
                'checkers' => [
                    'success' => [
                        'success' => true,
                        'message' => 'success'
                    ],
                    'failure' => [
                        'success' => false,
                        'message' => 'failure'
                    ]
                ]
            ],
            $subject->jsonSerialize()
        );
    }
}
