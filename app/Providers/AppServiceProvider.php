<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ResourceRepositoryInterface;
use App\Repositories\Eloquent\ResourceRepository;
use App\Services\ResourceUploadService;
use App\Strategies\FileProcessors\ImageProcessor;
use App\Strategies\FileProcessors\PdfProcessor;
use App\Strategies\FileProcessors\ZipProcessor;
use App\Strategies\FileProcessors\TarGzProcessor;
use App\Strategies\FileProcessors\RarProcessor;
use App\Strategies\FileProcessors\SevenZipProcessor;
use App\Strategies\FileProcessors\DefaultProcessor;
use App\Repositories\Contracts\AdminRepositoryInterface;
use App\Repositories\Eloquent\AdminRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. Enlazar interfaz con su implementación Eloquent (Repository Pattern)
        $this->app->bind(ResourceRepositoryInterface::class, ResourceRepository::class);

        // 2. Etiquetar las estrategias (Strategy Pattern)
        $this->app->tag([
            ImageProcessor::class,
            PdfProcessor::class,
            ZipProcessor::class,
            DefaultProcessor::class,
            TarGzProcessor::class,
            RarProcessor::class,
            SevenZipProcessor::class,
        ], 'file_processors');

        // 3. Resolver la inyección del array de procesadores en el Servicio
        $this->app->bind(ResourceUploadService::class, function ($app) {
            return new ResourceUploadService(
                $app->make(ResourceRepositoryInterface::class),
                $app->tagged('file_processors')
            );
        });

        $this->app->bind(
            AdminRepositoryInterface::class,
            AdminRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
