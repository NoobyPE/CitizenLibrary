<?php

namespace nooby\CitizenLib\utils;

use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;

trait UUID {

    public function uuid(): UuidInterface
    {
        return RamseyUuid::uuid4();
    }
    
}
