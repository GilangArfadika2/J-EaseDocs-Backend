<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
class AppServiceProvider extends ServiceProvider
{
    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Validator::extend('json_schema_validation', function ($attribute, $value, $parameters, $validator) {
            $schemaPath = resource_path('schema/' . implode('/', $parameters) . '.json');
            error_log($schemaPath);
            if (!File::exists($schemaPath)) {
                return false; // Schema file does not exist
            }
    
            // Validate JSON against schema using Justin Rainbow's JSON schema validator
            $validator = new \JsonSchema\Validator();
            $validator->validate(json_decode($value), (object)['$ref' => 'file://' . $schemaPath]);
    
            return $validator->isValid();
        });
    }

}
