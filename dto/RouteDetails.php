<?php

namespace GromIT\RoutesBrowser\Dto;

class RouteDetails
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string|null
     */
    public $action;

    /**
     * @var string|null
     */
    public $requestClass;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var \GromIT\RoutesBrowser\Dto\RouteParam[]
     */
    public $routeParams = [];

    /**
     * @var \GromIT\RoutesBrowser\Dto\RequestParam[]
     */
    public $requestParams = [];

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
