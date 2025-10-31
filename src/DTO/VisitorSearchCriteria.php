<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\DTO;

use Tourze\VisitorManageBundle\Enum\VisitorStatus;

class VisitorSearchCriteria
{
    public ?string $name = null;

    public ?string $mobile = null;

    public ?string $company = null;

    public ?\DateTime $appointmentFrom = null;

    public ?\DateTime $appointmentTo = null;

    public ?VisitorStatus $status = null;

    public int $page = 1;

    public int $limit = 20;
}
