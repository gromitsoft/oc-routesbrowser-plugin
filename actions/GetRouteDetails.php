<?php

namespace GromIT\RoutesBrowser\Actions;

use Closure;
use GromIT\RoutesBrowser\Dto\RequestParam;
use GromIT\RoutesBrowser\Dto\RouteDetails;
use GromIT\RoutesBrowser\Dto\RouteParam;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlock\Tags\PropertyRead;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;

class GetRouteDetails
{
    /**
     * @var \phpDocumentor\Reflection\DocBlockFactory
     */
    private $docBlockFactory;

    /**
     * @var \Illuminate\Routing\Route
     */
    private $route;

    /**
     * @var array
     */
    private $action;

    /**
     * @var \ReflectionFunction|\ReflectionMethod
     */
    private $actionReflection;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * @param \Illuminate\Routing\Route $route
     * @param string                    $method
     *
     * @return \GromIT\RoutesBrowser\Dto\RouteDetails
     * @throws \ReflectionException
     */
    public function execute(Route $route, string $method): RouteDetails
    {
        $this->route = $route;

        $routeDetailsData = [
            'uri'    => $this->route->uri,
            'method' => $method,
        ];

        $this->loadAction();

        if ($this->action === null) {
            return new RouteDetails($routeDetailsData);
        }

        $routeDetailsData['action'] = $this->route->getActionName();

        $actionDocComment = $this->getActionDocComment();

        if ($actionDocComment) {
            $routeDetailsData['description'] = $this->getActionDescription($actionDocComment);
        }

        $routeDetailsData['routeParams'] = $this->getRouteParams($actionDocComment);

        $requestParameterClass = $this->getRouteFormRequestParamClass();

        if ($requestParameterClass === null) {
            return new RouteDetails($routeDetailsData);
        }

        $routeDetailsData['requestClass']  = $requestParameterClass;
        $routeDetailsData['requestParams'] = $this->getRequestParams($requestParameterClass);

        return new RouteDetails($routeDetailsData);
    }

    private function getActionDocComment(): ?string
    {
        return $this->actionReflection
            ? $this->actionReflection->getDocComment()
            : null;
    }

    /**
     * @param string $actionDocComment
     *
     * @return string|null
     */
    private function getActionDescription(string $actionDocComment): ?string
    {
        return $this
            ->docBlockFactory
            ->create($actionDocComment)
            ->getSummary();
    }

    private function getRouteParams(string $actionDocComment): array
    {
        $params = [];

        /** @var ReflectionParameter[] $actionParameters */
        $actionParameters = collect($this->actionReflection->getParameters())
            ->mapWithKeys(function (ReflectionParameter $parameter) {
                return [$parameter->getName() => $parameter];
            })
            ->all();

        $docBlockParams = [];

        if ($actionDocComment) {
            $docBlock       = $this->docBlockFactory->create($actionDocComment);
            $docBlockParams = collect($docBlock->getTagsByName('param'))
                ->mapWithKeys(function (Param $param) {
                    return [
                        $param->getVariableName() => [
                            'name'        => $param->getVariableName(),
                            'type'        => $param->getType(),
                            'description' => (string)$param->getDescription(),
                        ]
                    ];
                })->all();
        }

        foreach ($this->route->parameterNames() as $parameterName) {
            $paramData = [];

            $paramData['name'] = $parameterName;

            $docType = Arr::get($docBlockParams, "$parameterName.type");

            if ($docType) {
                $paramData['type'] = $docType;
            } else {
                /** @var ReflectionParameter $actionParam */
                $actionParam = Arr::get($actionParameters, $parameterName);
                $type        = $actionParam ? $actionParam->getType() : null;

                if ($type && $type instanceof ReflectionNamedType) {
                    $paramData['type'] = $type->getName();
                }
            }

            $paramData['description'] = Arr::get($docBlockParams, "$parameterName.description");

            $params[] = new RouteParam($paramData);
        }

        return $params;
    }

    private function getRouteFormRequestParamClass(): ?string
    {
        if ($this->actionReflection) {
            return $this->findRequestParameter($this->actionReflection->getParameters());
        }

        return null;
    }

    /**
     * @param string $requestClass
     *
     * @return RequestParam[]
     * @throws \ReflectionException
     */
    private function getRequestParams(string $requestClass): array
    {
        $requestReflection = new ReflectionClass($requestClass);

        $comment = $requestReflection->getDocComment();

        if ($comment === false) {
            return [];
        }

        $docBlock = $this->docBlockFactory->create($comment);

        $properties = collect($docBlock->getTagsByName('property'))
            ->merge($docBlock->getTagsByName('property-read'));

        return collect($properties)
            ->map(function ($property) {
                /** @var  Property|PropertyRead $property */
                return new RequestParam([
                    'type'        => $property->getType(),
                    'name'        => $property->getVariableName(),
                    'description' => $property->getDescription()
                ]);
            })
            ->all();
    }

    /**
     * @param \ReflectionParameter[] $parameters
     *
     * @return string|null
     */
    private function findRequestParameter(array $parameters): ?string
    {
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type
                && $type instanceof ReflectionNamedType
                && is_subclass_of($type->getName(), Request::class)
            ) {
                return $type->getName();
            }
        }

        return null;
    }

    /**
     * @throws \ReflectionException
     */
    private function loadAction(): void
    {
        $this->action = $this->route->getAction();

        $uses = Arr::get($this->action, 'uses');

        if ($uses instanceof Closure) {
            $this->actionReflection = new ReflectionFunction($uses);
            return;
        }

        if (is_string($uses)) {
            [$class, $method] = explode('@', $uses);
            $classReflection        = new ReflectionClass($class);
            $this->actionReflection = $classReflection->getMethod($method);
        }
    }
}
