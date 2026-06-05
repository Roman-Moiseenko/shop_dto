<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Application\Actions\User\ChangeUserCredentialsUseCase;
use App\Modules\Auth\Application\Actions\User\RegisterUserClientUseCase;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\FreelanceRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\StaffRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Database\Seeders\AuthRoleSeeder;
use App\Modules\Auth\Domain\Exceptions\ClientNotFoundException;
use App\Modules\Auth\Domain\Exceptions\RoleInvalidArgumentException;
use App\Modules\Auth\Domain\Exceptions\StaffNotFoundException;
use App\Modules\Auth\Domain\Services\PasswordHasherInterface;
use App\Modules\Auth\Domain\Services\PermissionProviderInterface;
use App\Modules\Auth\Domain\Services\RoleRepositoryInterface;
use App\Modules\Auth\Infrastructure\Persistence\ClientRepository;
use App\Modules\Auth\Infrastructure\Persistence\FreelanceRepository;
use App\Modules\Auth\Infrastructure\Persistence\RoleRepository;
use App\Modules\Auth\Infrastructure\Persistence\StaffRepository;
use App\Modules\Auth\Infrastructure\Persistence\UserRepository;
use App\Modules\Auth\Infrastructure\Services\LaravelPasswordHasher;
use App\Modules\Auth\Infrastructure\Services\PermissionProvider;
use App\Modules\Auth\Presentation\Console\Commands\AdminCreateCommand;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Service Provider for Auth module
 *
 * This provider handles automatic registration of all module components:
 * commands, translations, configuration, events, routes, views, migrations,
 * factories and seeders.
 *
 * @package App\Modules\Auth\Providers
 * @author Easy Module Generator
 * @version 1.0.0
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Module name
     */
    protected string $name = 'Auth';

    /**
     * Module base path within app/
     */
    protected string $basePath = 'Modules/Auth';

    /**
     * Module base namespace
     */
    protected string $baseNamespace = 'App\Modules\Auth';

    /**
     * Namespace used for views and translations
     */
    protected string $scopeNamespace = 'auth';

    /**
     * Default middlewares for web routes
     */
    protected array $webMiddlewares = ['web'];

    /**
     * Default middlewares for API routes
     */
    protected array $apiMiddlewares = ['api'];

    /**
     * Default middlewares for console routes
     */
    protected array $consoleMiddlewares = [];

    /**
     * Boot the service provider - Register all module components
     *
     * CORRECTION: Signature sans type de retour (classe parente n'en a pas)
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerEvents();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerFactories();
        $this->registerSeeders();

        // Регистрируем обработчик исключения только для этого модуля
        $this->app->booted(function () {
            $handler = $this->app->make(ExceptionHandler::class);
            $handler->renderable(function (StaffNotFoundException $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            });
            $handler->renderable(function (ClientNotFoundException $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
            });
            $handler->renderable(function (RoleInvalidArgumentException $e) {
                return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            });


        });

    }

    /**
     * Register services in the IoC container
     *
     * CORRECTION: Signature sans type de retour (classe parente n'en a pas)
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            StaffRepositoryInterface::class,
            StaffRepository::class
        );
        $this->app->bind(
            ClientRepositoryInterface::class,
            ClientRepository::class
        );
        $this->app->bind(
            FreelanceRepositoryInterface::class,
            FreelanceRepository::class
        );
        $this->app->bind(
            PasswordHasherInterface::class,
            LaravelPasswordHasher::class
        );
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );
        $this->app->bind(
            PermissionProviderInterface::class,
            PermissionProvider::class
        );
        $this->app->when(ChangeUserCredentialsUseCase::class)
            ->needs('$frontendUrl')
            ->give(config('app.frontend_url'));
        $this->app->when(RegisterUserClientUseCase::class)
            ->needs('$frontendUrl')
            ->give(config('app.frontend_url'));
        // Register module-specific services
    }

    // =====================================================================
    // ARTISAN COMMANDS
    // =====================================================================

    /**
     * Register Artisan commands for the module
     *
     * Expected format: [CommandClass::class, AnotherCommand::class]
     */
    protected function registerCommands(): void
    {
        $this->commands([
            AdminCreateCommand::class,
        ]);
    }

    // =====================================================================
    // TRANSLATIONS
    // =====================================================================

    /**
     * Register translation files for the module
     *
     * First searches in resources/lang/modules/{scope}
     * then in the module's lang directory
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/' . $this->scopeNamespace);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->scopeNamespace);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $moduleLangPath = $this->getPath('lang');
            $this->loadTranslationsFrom($moduleLangPath, $this->scopeNamespace);
            $this->loadJsonTranslationsFrom($moduleLangPath);
        }
    }

    // =====================================================================
    // CONFIGURATION
    // =====================================================================

    /**
     * Recursively register all configuration files from the module
     *
     * Scans the config directory and registers each PHP file as configuration
     * Subdirectories are converted to dot notation keys (e.g., auth/providers.php -> auth.providers)
     */
    protected function registerConfig()
    {
        $configPath = $this->getPath('config');

        if (!is_dir($configPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($configPath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $this->processConfigFile($file, $configPath);
        }
    }

    /**
     * Process an individual configuration file
     *
     * @param \SplFileInfo $file Configuration file
     * @param string $configPath Root config path
     */
    private function processConfigFile(\SplFileInfo $file, string $configPath)
    {
        $relativePath = str_replace($configPath . '/', '', $file->getPathname());
        $configKey = str_replace(['/', '.php'], ['.', ''], $relativePath);

        $segments = explode('.', $this->scopeNamespace . '.' . $configKey);
        $normalizedSegments = $this->removeAdjacentDuplicates($segments);

        $key = ($relativePath === 'config.php')
            ? $this->scopeNamespace
            : implode('.', $normalizedSegments);

        $this->publishes([$file->getPathname() => config_path($relativePath)], 'config');
        $this->mergeConfigFrom($file->getPathname(), $key);
    }

    /**
     * Remove adjacent duplicate segments from an array
     *
     * @param array<string> $segments
     * @return array<string>
     */
    private function removeAdjacentDuplicates(array $segments): array
    {
        $normalized = [];

        foreach ($segments as $segment) {
            if (end($normalized) !== $segment) {
                $normalized[] = $segment;
            }
        }

        return $normalized;
    }

    /**
     * Recursively merge module configuration with existing configuration
     *
     * CORRECTION: Override de la méthode parente sans changer la signature
     */
    protected function mergeConfigFrom($path, $key)
    {
        $existing = config($key, []);
        $moduleConfig = require $path;

        config([$key => array_replace_recursive($existing, $moduleConfig)]);
    }

    // =====================================================================
    // EVENTS
    // =====================================================================

    /**
     * Register events and listeners for the module
     */
    public function registerEvents()
    {
        // Event::listen(EventClass::class, ListenerClass::class);
        // Event::subscribe(SubscriberClass::class);
    }

    // =====================================================================
    // ROUTES
    // =====================================================================

    /**
     * Register all module routes (web, api, console)
     */
    public function registerRoutes(): void
    {
        $this->registerWebRoutes();
        $this->registerApiRoutes();
        $this->registerConsoleRoutes();
    }

    /**
     * Register web routes with configurable middlewares
     */
    public function registerWebRoutes()
    {
        $routePath = $this->getPath('routes/web.php');

        if (file_exists($routePath)) {
            Route::middleware($this->getWebMiddlewares())->group($routePath);
        }
    }

    /**
     * Register API routes with configurable middlewares
     */
    public function registerApiRoutes()
    {
        $routePath = $this->getPath('routes/api.php');

        if (file_exists($routePath)) {
            Route::middleware($this->getApiMiddlewares())->group($routePath);
        }
    }

    /**
     * Register console routes (Artisan) with configurable middlewares
     */
    public function registerConsoleRoutes()
    {
        $routePath = $this->getPath('routes/console.php');

        if (file_exists($routePath)) {
            Route::middleware($this->getConsoleMiddlewares())->group($routePath);
        }
    }

    /**
     * Get middlewares for web routes
     */
    protected function getWebMiddlewares(): array
    {
        return $this->webMiddlewares;
    }

    /**
     * Get middlewares for API routes
     */
    protected function getApiMiddlewares(): array
    {
        return $this->apiMiddlewares;
    }

    /**
     * Get middlewares for console routes
     */
    protected function getConsoleMiddlewares(): array
    {
        return $this->consoleMiddlewares;
    }

    // =====================================================================
    // VIEWS AND COMPONENTS
    // =====================================================================

    /**
     * Register views and Blade components for the module
     *
     * Loads views from two possible locations in order of priority:
     * 1. External: resources/views/modules/{scope}/ (Laravel standard structure)
     * 2. Internal: Module's own views directory (clean architecture approach)
     */
    public function registerViews()
    {
        $sourcePath = $this->getPath('Presentation/resources/views');
        $viewPaths = array_merge($this->getExternalModuleViewPaths(), [$sourcePath]);

        $this->loadViewsFrom($viewPaths, $this->scopeNamespace);

        // Register Blade components
        $componentsNamespace = $this->getNamespace('Presentation/Views/Components');
        Blade::componentNamespace($componentsNamespace, $this->scopeNamespace);
    }

    /**
     * Get external module view paths from Laravel's standard view directories
     *
     * Searches for module views in Laravel's configured view paths under /modules/{scope}/
     * This allows views to be organized either within the module structure or
     * in Laravel's standard resources/views/modules/{scope}/ directory
     */
    private function getExternalModuleViewPaths(): array
    {
        $paths = [];

        foreach (config('view.paths') as $path) {
            $modulePath = $path . '/modules/' . $this->scopeNamespace;
            if (is_dir($modulePath)) {
                $paths[] = $modulePath;
            }
        }

        return $paths;
    }

    // =====================================================================
    // DATABASE
    // =====================================================================

    /**
     * Register module migrations
     */
    public function registerMigrations()
    {
        $migrationsPath = $this->getPath('Database/Migrations');

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    /**
     * Register Eloquent factories for the module
     */
    public function registerFactories()
    {
        $factoriesPath = $this->getPath('Database/Factories');

        if (is_dir($factoriesPath)) {
            $this->loadFactoriesFrom($factoriesPath);
        }
    }

    /**
     * Register module seeders
     */
    public function registerSeeders()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->app->afterResolving('seed.handler', function ($handler) {
            $handler->register([
                AuthRoleSeeder::class,
            ]);
        });
    }

    // =====================================================================
    // UTILITIES
    // =====================================================================

    /**
     * Get the services provided by this provider
     *
     * CORRECTION: Signature doit correspondre à la classe parente
     */
    public function provides()
    {
        return [];
    }

    /**
     * Build full path to a module directory
     */
    private function getPath(string $path): string
    {
        return app_path($this->basePath . '/' . $path);
    }

    /**
     * Build full namespace for a module part
     */
    private function getNamespace(string $namespace): string
    {
        return $this->baseNamespace . '\\' . str_replace('/', '\\', $namespace);
    }
}
