<?php

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @return list<array{file: string, line: int, method: string, route: string, allowed: list<string>}>
 */
function inertiaFormMethodMismatches(): array
{
    $pattern = '/\b(post|put|patch|delete)\(\s*route\(\s*[\'"]([\w.-]+)[\'"]/';
    $mismatches = [];

    $files = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['tsx', 'jsx', 'ts', 'js'], true))
        ->reject(fn (SplFileInfo $file): bool => $file->getFilename() === 'ziggy.js');

    foreach ($files as $file) {
        foreach (preg_split('/\R/', $file->getContents()) as $index => $line) {
            if (! preg_match_all($pattern, $line, $matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($matches as [, $method, $name]) {
                $route = Route::getRoutes()->getByName($name);

                if ($route === null) {
                    continue;
                }

                $allowed = collect(Route::getRoutes()->getRoutes())
                    ->filter(fn ($candidate): bool => $candidate->uri() === $route->uri())
                    ->flatMap(fn ($candidate): array => $candidate->methods())
                    ->unique()
                    ->values()
                    ->all();

                if (! in_array(strtoupper($method), $allowed, true)) {
                    $mismatches[] = [
                        'file' => str_replace('\\', '/', $file->getRelativePathname()),
                        'line' => $index + 1,
                        'method' => strtoupper($method),
                        'route' => $name,
                        'allowed' => $allowed,
                    ];
                }
            }
        }
    }

    return $mismatches;
}

test('inertia form submissions use an http method their route accepts', function () {
    expect(inertiaFormMethodMismatches())->toBe([]);
});

test('profile update only accepts patch', function () {
    expect(Route::getRoutes()->getByName('profile.update')->methods())->toBe(['PATCH']);

    $this->actingAs(User::factory()->create())
        ->post(route('profile.update'), ['name' => 'Test User'])
        ->assertMethodNotAllowed();
});
