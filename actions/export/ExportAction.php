<?php

namespace GromIT\RoutesBrowser\Actions\Export;

use GromIT\RoutesBrowser\Actions\ListRoutes;

abstract class ExportAction
{
    /**
     * @var \GromIT\RoutesBrowser\Actions\ListRoutes
     */
    protected $listRoutes;

    public function __construct(ListRoutes $listRoutes)
    {
        $this->listRoutes = $listRoutes;
    }
}
