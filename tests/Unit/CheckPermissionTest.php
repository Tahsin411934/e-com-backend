<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Modules\Identity\Http\Middleware\CheckPermission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckPermissionTest extends TestCase
{
    public function test_resource_wildcard_resolves_view_actions_to_view_permission(): void
    {
        $request = $this->requestFor('index', ['coupons.view']);

        $response = (new CheckPermission)->handle(
            $request,
            fn () => new Response('allowed'),
            'coupons.*',
        );

        $this->assertSame('allowed', $response->getContent());
    }

    public function test_view_permission_cannot_pass_a_resource_delete_action(): void
    {
        $request = $this->requestFor('destroy', ['coupons.view']);

        try {
            (new CheckPermission)->handle(
                $request,
                fn () => new Response('allowed'),
                'coupons.*',
            );

            $this->fail('The delete action should have been denied.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_resource_delete_action_requires_delete_permission(): void
    {
        $request = $this->requestFor('destroy', ['coupons.delete']);

        $response = (new CheckPermission)->handle(
            $request,
            fn () => new Response('allowed'),
            'coupons.*',
        );

        $this->assertSame('allowed', $response->getContent());
    }

    private function requestFor(string $action, array $permissions): Request
    {
        $request = Request::create('/test');
        $request->setRouteResolver(fn () => new class($action)
        {
            public function __construct(private readonly string $action) {}

            public function getActionMethod(): string
            {
                return $this->action;
            }
        });

        $permissionModels = collect($permissions)->map(
            fn (string $name) => (object) ['name' => $name]
        );
        $request->setUserResolver(fn () => (object) [
            'roles' => collect([
                (object) [
                    'name' => 'Staff',
                    'permissions' => $permissionModels,
                ],
            ]),
        ]);

        return $request;
    }
}
