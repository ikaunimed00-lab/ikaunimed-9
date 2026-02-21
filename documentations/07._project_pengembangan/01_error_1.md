# Error - Internal Server Error

Class "Filament\Tables\Actions\EditAction" not found

PHP 8.4.16
Laravel 12.51.0
ikaunimed-9.or.id.test

## Stack Trace

0 - app\Filament\Resources\OrganizationProgramResource.php:101
1 - vendor\filament\filament\src\Resources\Resource.php:65
2 - vendor\filament\filament\src\Resources\Pages\ListRecords.php:210
3 - vendor\filament\tables\src\Concerns\InteractsWithTable.php:47
4 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:36
5 - vendor\laravel\framework\src\Illuminate\Container\Util.php:43
6 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:96
7 - vendor\laravel\framework\src\Illuminate\Container\BoundMethod.php:35
8 - vendor\livewire\livewire\src\Wrapped.php:23
9 - vendor\livewire\livewire\src\Features\SupportLifecycleHooks\SupportLifecycleHooks.php:182
10 - vendor\livewire\livewire\src\Features\SupportLifecycleHooks\SupportLifecycleHooks.php:39
11 - vendor\livewire\livewire\src\ComponentHook.php:19
12 - vendor\livewire\livewire\src\ComponentHookRegistry.php:45
13 - vendor\livewire\livewire\src\EventBus.php:87
14 - vendor\livewire\livewire\src\helpers.php:98
15 - vendor\livewire\livewire\src\Mechanisms\HandleComponents\HandleComponents.php:76
16 - vendor\livewire\livewire\src\LivewireManager.php:102
17 - vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:19
18 - vendor\livewire\livewire\src\Features\SupportPageComponents\SupportPageComponents.php:118
19 - vendor\livewire\livewire\src\Features\SupportPageComponents\HandlesPageComponents.php:14
20 - vendor\laravel\framework\src\Illuminate\Routing\ControllerDispatcher.php:46
21 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:265
22 - vendor\laravel\framework\src\Illuminate\Routing\Route.php:211
23 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:822
24 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
25 - vendor\filament\filament\src\Http\Middleware\DispatchServingFilamentEvent.php:15
26 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
27 - vendor\filament\filament\src\Http\Middleware\DisableBladeIconComponents.php:14
28 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
29 - vendor\laravel\framework\src\Illuminate\Routing\Middleware\SubstituteBindings.php:50
30 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
31 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken.php:87
32 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
33 - vendor\laravel\framework\src\Illuminate\Session\Middleware\AuthenticateSession.php:70
34 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
35 - vendor\laravel\framework\src\Illuminate\Auth\Middleware\Authenticate.php:63
36 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
37 - vendor\laravel\framework\src\Illuminate\View\Middleware\ShareErrorsFromSession.php:48
38 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
39 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:120
40 - vendor\laravel\framework\src\Illuminate\Session\Middleware\StartSession.php:63
41 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
42 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse.php:36
43 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
44 - vendor\laravel\framework\src\Illuminate\Cookie\Middleware\EncryptCookies.php:74
45 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
46 - vendor\filament\filament\src\Http\Middleware\SetUpPanel.php:19
47 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
48 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
49 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:821
50 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:800
51 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:764
52 - vendor\laravel\framework\src\Illuminate\Routing\Router.php:753
53 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:200
54 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:180
55 - vendor\livewire\livewire\src\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware.php:19
56 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
57 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
58 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull.php:31
59 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
60 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TransformsRequest.php:21
61 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\TrimStrings.php:51
62 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
63 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePostSize.php:27
64 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
65 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance.php:109
66 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
67 - vendor\laravel\framework\src\Illuminate\Http\Middleware\HandleCors.php:61
68 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
69 - vendor\laravel\framework\src\Illuminate\Http\Middleware\TrustProxies.php:58
70 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
71 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks.php:22
72 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
73 - vendor\laravel\framework\src\Illuminate\Http\Middleware\ValidatePathEncoding.php:26
74 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:219
75 - vendor\laravel\framework\src\Illuminate\Pipeline\Pipeline.php:137
76 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:175
77 - vendor\laravel\framework\src\Illuminate\Foundation\Http\Kernel.php:144
78 - vendor\laravel\framework\src\Illuminate\Foundation\Application.php:1220
79 - public\index.php:20
80 - C:\Program Files\Herd\resources\app.asar.unpacked\resources\valet\server.php:139

## Request

GET /admin/organization-programs

## Headers

* **cookie**: XSRF-TOKEN=eyJpdiI6IllrYmRHUHFuYmwzbnFYUXNMRWVKRkE9PSIsInZhbHVlIjoiYnJDQzFEUFNsSDF0TkdsTVkrNmdwOWRsUGJXRm9Jekh3WkU4ck1tdVcxbGhOZVNrSXVHMVI3ZU5BUWZpUjdhTGlMNjBwbWgwTjdXMXVTeGdGUWlBNEl3S244WWRPQkM5L0NzYVN4MUdQMll1UnJpbVdFL0ZIRXhxTXpkWmtLWXIiLCJtYWMiOiJiNmU1YjA0ZTZhNGNmNjY1NTYwNzY0NmIyNzEwODVmZGU3MDE1MzYyOGU0Yzc4ZTYwOGQxZjU2ZjJkNjlhY2ZhIiwidGFnIjoiIn0%3D; ika-unimed-session=eyJpdiI6InZtSyttbkV3L0RaQk5veUNnejA1REE9PSIsInZhbHVlIjoiRE5GWG1HNS9WZXdnaXlQcmlIb2JhQ2FnZTUzZHQ5aDZrMTJnb280T3U4ZytyRUdUbTJ5eC8zLzFaM09UY0llODU5d3R5ek5hOWpKeEpYL2Z2bG1tSmJlcERESi9HY1o3Um9yM1FDcjA3anMxM2tWbnF6dGlMS2YvMkp4TjBITXUiLCJtYWMiOiI3MzlhNjZhYzAzYTdkYTdmMjcwZDkwOTVhNmVmYzM0NmJmZDM2ZTdmOGU2Y2Q4MmJiNGZmNGI1MTA5M2JmMzBkIiwidGFnIjoiIn0%3D
* **accept-language**: en-US,en;q=0.9
* **accept-encoding**: gzip, deflate
* **referer**: http://ikaunimed-9.or.id.test/
* **accept**: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
* **user-agent**: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36
* **upgrade-insecure-requests**: 1
* **connection**: keep-alive
* **host**: ikaunimed-9.or.id.test

## Route Context

controller: App\Filament\Resources\OrganizationProgramResource\Pages\ListOrganizationPrograms
route name: filament.admin.resources.organization-programs.index
middleware: panel:admin, Illuminate\Cookie\Middleware\EncryptCookies, Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse, Illuminate\Session\Middleware\StartSession, Filament\Http\Middleware\AuthenticateSession, Illuminate\View\Middleware\ShareErrorsFromSession, Illuminate\Foundation\Http\Middleware\VerifyCsrfToken, Illuminate\Routing\Middleware\SubstituteBindings, Filament\Http\Middleware\DisableBladeIconComponents, Filament\Http\Middleware\DispatchServingFilamentEvent, Filament\Http\Middleware\Authenticate

## Route Parameters

No route parameter data available.

## Database Queries

* sqlite - select * from "sessions" where "id" = 'N7HeQPwZCQJpVSMXobXB8dkaRo9FGW2zT85rSELI' limit 1 (6.88 ms)
* sqlite - select * from "users" where "id" = 1 limit 1 (0.45 ms)
