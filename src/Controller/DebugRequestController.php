<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class DebugRequestController
{
    #[Route(
        path: '/debug/request/{slug}',
        name: 'app_debug_request',
        requirements: ['slug' => '[a-z0-9_-]+'],
        defaults: ['page' => 1],
        methods: ['GET']
    )]
    public function __invoke(Request $request, string $slug, int $page = 1): JsonResponse
    {
        return new JsonResponse([
            'method' => $request->getMethod(),
            'pathInfo' => $request->getPathInfo(),
            'query' => $request->query->all(),
            'request' => $request->request->all(),
            'attributes' => $request->attributes->all(),
            'slug_argument' => $slug,
            'page_argument' => $page,
        ]);
    }

    #[Route(
        path: '/debug/request/{slug}/page/{page}',
        name: 'app_debug_request_page',
        requirements: [
            'slug' => '[a-z0-9_-]+',
            'page' => '\d+',
        ],
        methods: ['GET']
    )]
    public function withPage(Request $request, string $slug, int $page): JsonResponse
    {
        return new JsonResponse([
            'query' => $request->query->all(),
            'attributes' => $request->attributes->all(),
            'slug_argument' => $slug,
            'page_argument' => $page,
        ]);
    }
}
