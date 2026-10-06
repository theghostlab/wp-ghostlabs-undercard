<?php

namespace GhostLabs\WPKit\RestRoute;

use Attribute;

#[Attribute]
class CollectRoute
{
    public function __construct(public string $group = 'ghostlabs-routes')
    {
    }
}
