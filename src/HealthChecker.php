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

namespace OAT\Library\HealthCheck;

use OAT\Library\HealthCheck\Checker\CheckerInterface;
use OAT\Library\HealthCheck\Result\CheckerResult;
use OAT\Library\HealthCheck\Result\CheckerResultCollection;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class HealthChecker
{
    /** @var CheckerInterface[] */
    private $checkers;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(iterable $checkers = [], ?LoggerInterface $logger = null)
    {
        foreach ($checkers as $index => $checker) {
            $this->registerChecker($checker, is_string($index) ? $index : null);
        }

        $this->logger = $logger ?? new NullLogger();
    }

    public function registerChecker(CheckerInterface $checker, string $identifier = null): self
    {
        $this->checkers[$identifier ?? $checker->getIdentifier()] = $checker;

        return $this;
    }

    public function performChecks(): CheckerResultCollection
    {
        $collection = new CheckerResultCollection();

        foreach ($this->checkers as $identifier => $checker) {
            try {
                $result = $checker->check();

                if ($result->isSuccess()) {
                    $this->logger->info($result->getMessage());
                } else {
                    $this->logger->error($result->getMessage());
                }
            } catch (Throwable $exception) {
                $message = $exception->getMessage();
                $this->logger->error($message);
                $result = new CheckerResult(false, $message);
            }

            $collection->add($identifier, $result);
        }

        return $collection;
    }
}
