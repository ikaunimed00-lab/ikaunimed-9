<?php

namespace App\Providers;

use App\Models\AlumniPost;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\JobVacancy;
use App\Models\News;
use App\Models\Partnership;
use App\Models\Scholarship;
use App\Policies\AlumniPostPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\JobVacancyPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PartnershipPolicy;
use App\Policies\ScholarshipPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AlumniPost::class => AlumniPostPolicy::class,
        Course::class => CoursePolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        News::class => NewsPolicy::class,
        JobVacancy::class => JobVacancyPolicy::class,
        Scholarship::class => ScholarshipPolicy::class,
        Partnership::class => PartnershipPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
