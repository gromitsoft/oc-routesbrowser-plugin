<?php

namespace GromIT\RoutesBrowser\Actions\Export;

use GromIT\RoutesBrowser\Dto\RequestParam;
use GromIT\RoutesBrowser\Dto\RouteDetails;
use October\Rain\Argon\Argon;
use stdClass;

class GenerateThunderCollection extends ExportAction
{
    /**
     * @var \Illuminate\Support\Collection|\GromIT\RoutesBrowser\Dto\RouteDetails[]
     */
    private $routes;

    /**
     * @var array
     */
    private $exportData = [];

    public function execute()
    {
        $this->fillHeader();
        $this->fillRoutes();

        return json_encode($this->exportData);
    }

    private function fillHeader()
    {
        $this->exportData = [
            'client'         => 'Thunder Client',
            'collectionName' => config('app.name'),
            'dateExported'   => Argon::now()->toIso8601ZuluString(),
            'version'        => '1.1',
            'folders'        => [],
            'requests'       => [],
        ];
    }

    private function fillRoutes()
    {
        $sort = 1;

        foreach ($this->listRoutes->execute() as $route) {
            $docs = $this->generateRouteDocs($route);

            $collectionId = \Str::uuid();

            $request = [
                '_id'         => \Str::uuid(),
                'colId'       => $collectionId,
                'containerId' => '',
                'name'        => $route->description ?: $route->uri,
                'url'         => '{{ APP_URL }}/' . $route->uri,
                'method'      => $route->method,
                'sortNum'     => $sort++,
                'created'     => Argon::now()->toIso8601ZuluString(),
                'modified'    => Argon::now()->toIso8601ZuluString(),
                'headers'     => [],
                'tests'       => [],
                'docs'        => $docs,
            ];

            $params = [];
            $body   = [];

            foreach ($route->routeParams as $param) {
                $params[] = [
                    'name'   => $param->name,
                    'isPath' => true,
                ];
            }

            switch (strtolower($route->method)) {
                case 'get':
                    foreach ($route->requestParams as $param) {
                        $params[] = [
                            'name'   => $param->name,
                            'isPath' => false,
                        ];
                    }
                    break;
                case 'post':
                    $hasFile = collect($route->requestParams)->reduce(function ($acc, RequestParam $param) {
                        return $acc || str_contains(strtolower($param->type), 'file');
                    }, false);

                    if ($hasFile) {
                        $body = [
                            'type'  => 'formdata',
                            'raw'   => '',
                            'form'  => [],
                            'files' => [],
                        ];

                        foreach ($route->requestParams as $param) {
                            if (str_contains(strtolower($param->type), 'file')) {
                                $body['files'][] = [
                                    'name' => $param->name,
                                ];
                            } else {
                                $body['form'][] = [
                                    'name' => $param->name,
                                ];
                            }
                        }
                    } else {
                        $raw = [];
                        foreach ($route->requestParams as $param) {
                            $raw[$param->name] = '';
                        }

                        $body = [
                            'type' => 'json',
                            'raw'  => json_encode($raw, JSON_PRETTY_PRINT),
                        ];
                    }
                    break;
                case 'put':
                case 'patch':
                case 'delete':
                    $raw = [];
                    foreach ($route->requestParams as $param) {
                        $raw[$param->name] = '';
                    }

                    $body = [
                        'type' => 'json',
                        'raw'  => json_encode($raw, JSON_PRETTY_PRINT),
                    ];
                    break;
            }

            $request['params'] = $params;
            $request['body']   = empty($body) ? new stdClass : $body;

            $this->exportData['requests'][] = $request;
        }
    }

    private function generateRouteDocs(RouteDetails $route)
    {
        $rows = [$route->description];

        if ($route->action) {
            $rows[] = '';
            $rows[] = 'Handler: ' . $route->action;
        }

        if ($route->requestClass) {
            $rows[] = '';
            $rows[] = 'Request: ' . $route->requestClass;
        }

        if ($route->routeParams) {
            $rows[] = '';
            $rows[] = '**Route params:**';
            foreach ($route->routeParams as $param) {
                $rows[] = '';
                $rows[] = $param->name . ': ' . $param->type . ' ' . $param->description;
            }
        }

        if ($route->requestParams) {
            $rows[] = '';
            $rows[] = '**Request params:**';
            foreach ($route->requestParams as $param) {
                $rows[] = '';
                $rows[] = $param->name . ': ' . $param->type . ' ' . $param->description;
            }
        }

        return implode("\n", $rows);
    }
}
