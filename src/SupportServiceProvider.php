<?php

namespace VisioSoft\Support;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SupportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('support')
            ->hasConfigFile()
            ->hasMigrations([
                'create_partner_support_table',
                'create_partner_support_replies_table',
            ]);
    }
}
