<?php

namespace App\Http\Middleware;

use App\Models\Tontine;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'authorization' => fn(): array => $this->authorization($request),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
            'query' => $request->query->all(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'translations' => fn(): array => $this->getTranslations(),

        ];
    }

    private function getTranslations(): array
    {
        $locale = app()->getLocale();

        $loader = function () use ($locale): array {
            $translations = [];

            foreach (glob(lang_path("{$locale}/*.php")) ?: [] as $file) {
                $group = basename($file, '.php');

                $translations[$group] = require $file;
            }

            $jsonTranslationsPath = lang_path("{$locale}.json");
            if (file_exists($jsonTranslationsPath)) {
                $jsonTranslations = json_decode(file_get_contents($jsonTranslationsPath), true);

                $translations = array_merge($jsonTranslations, $translations);
            }

            return $translations;
        };

        if (app()->isLocal()) {
            return $loader();
        }

        return cache()->rememberForever(
            "translations.{$locale}",
            $loader
        );
    }

    private function authorization(Request $request)
    {
        $user = $request->user();

        if ($user == null) {
            return [
                'tontine_id' => null,
                'roles' => [],
                'permissions' => []
            ];
        }

        $tontine = $request->route('tontine');

        if (!$tontine instanceof Tontine) {
            return [
                'tontine_id' => null,
                'roles' => [],
                'permissions' => [],
            ];
        }

        $previousTeamId = getPermissionsTeamId();

        try {
            setPermissionsTeamId($tontine->id);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');

            return [
                'tontine_id' => $tontine->id,
                'roles' => $user
                    ->roles
                    ->pluck('name')
                    ->values()
                    ->all(),

                'permissions' => $user
                    ->getAllPermissions()
                    ->pluck('name')
                    ->values()
                    ->all(),
            ];
        } finally {
            setPermissionsTeamId($previousTeamId);

            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
        }
    }
}
