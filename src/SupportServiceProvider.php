<?php

namespace VisioSoft\Support;

use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use VisioSoft\Support\Models\PartnerSupport;
use VisioSoft\Support\Models\PartnerSupportReply;
use VisioSoft\Support\Policies\PartnerSupportPolicy;
use VisioSoft\Support\Policies\PartnerSupportReplyPolicy;

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

    public function bootingPackage()
    {
        // Register policies
        Gate::policy(PartnerSupport::class, PartnerSupportPolicy::class);
        Gate::policy(PartnerSupportReply::class, PartnerSupportReplyPolicy::class);
    }
}
