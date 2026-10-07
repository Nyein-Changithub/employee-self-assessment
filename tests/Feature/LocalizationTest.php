<?php

namespace Tests\Feature;

use App\Models\AssessmentPeriod;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use DatabaseTransactions;

    /** Keys looked up dynamically (e.g. __($labels['plural'])) that a source scan cannot see. */
    private const DYNAMIC_KEYS = [
        'Departments', 'Department', 'Add Department', 'Create Department', 'Edit Department', 'Department Details',
        'Positions', 'Position', 'Add Position', 'Create Position', 'Edit Position', 'Position Details',
        'Assessment Periods', 'Submissions', 'Employees', 'Dashboard', 'Questions', 'Master Data', 'Access Control', 'Custom',
        'Full Name', 'Employee ID', 'Email', 'Pagination Navigation', 'Go to page :page',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed(RbacSeeder::class);
    }

    private function langJson(string $locale): array
    {
        return json_decode(file_get_contents(lang_path("$locale.json")), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return list<string> every literal __('...') key used in views and app code */
    private function usedKeys(): array
    {
        $keys = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path('resources/views'), \FilesystemIterator::SKIP_DOTS));
        $paths = [];
        foreach ($files as $f) { $paths[] = $f->getPathname(); }
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path(), \FilesystemIterator::SKIP_DOTS)) as $f) { $paths[] = $f->getPathname(); }

        foreach ($paths as $path) {
            if (! str_ends_with($path, '.php')) { continue; }
            preg_match_all('/__\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/', file_get_contents($path), $m);
            foreach (array_merge($m[1], $m[2]) as $k) {
                if ($k !== '') { $keys[] = stripslashes($k); }
            }
        }

        return array_values(array_unique($keys));
    }

    public function test_every_ui_string_has_an_english_and_myanmar_translation(): void
    {
        $en = $this->langJson('en');
        $mm = $this->langJson('mm');

        // 'group.key' lookups (pagination.next, validation.required, ...) live in lang/{locale}/*.php, tested separately.
        $json = fn (string $k) => ! preg_match('/^(pagination|validation|auth|passwords)\./', $k);
        $needed = array_values(array_filter(array_unique([...$this->usedKeys(), ...self::DYNAMIC_KEYS]), $json));
        $this->assertSame([], array_values(array_diff($needed, array_keys($mm))), 'Missing from lang/mm.json');
        $this->assertSame([], array_values(array_diff($needed, array_keys($en))), 'Missing from lang/en.json');
    }

    public function test_json_files_are_in_sync_and_never_blank(): void
    {
        $en = $this->langJson('en');
        $mm = $this->langJson('mm');

        $this->assertSame([], array_values(array_diff(array_keys($en), array_keys($mm))), 'in en.json but not mm.json');
        $this->assertSame([], array_values(array_diff(array_keys($mm), array_keys($en))), 'in mm.json but not en.json');
        $this->assertSame([], array_keys(array_filter($mm, fn ($v) => trim((string) $v) === '')), 'blank mm values');
        // Placeholders such as :count must survive translation.
        foreach ($mm as $key => $value) {
            preg_match_all('/:[a-z]+/', $key, $a);
            preg_match_all('/:[a-z]+/', $value, $b);
            $this->assertEqualsCanonicalizing($a[0], $b[0], "placeholder mismatch in '$key'");
        }
    }

    public function test_validation_pagination_and_auth_messages_are_translated(): void
    {
        app()->setLocale('mm');

        $this->assertStringContainsString('လိုအပ်', __('validation.required', ['attribute' => 'x']));
        $this->assertStringContainsString('ရှေ့သို့', __('pagination.previous'));
        $this->assertNotSame('auth.failed', __('auth.failed'));
        $this->assertStringContainsString('အီးမေးလ်', trans('validation.attributes.email'));

        app()->setLocale('en');
        $this->assertStringContainsString('required', __('validation.required', ['attribute' => 'x']));
    }

    public function test_myanmar_validation_errors_reach_the_user(): void
    {
        // admin login form
        $res = $this->withSession(['locale' => 'mm'])->post('/admin/login', []);
        $res->assertSessionHasErrors(['email', 'password']);
        $this->assertStringContainsString('ဖြည့်ရန်', session('errors')->first('email'));
        $this->assertStringNotContainsString('field is required', session('errors')->first('email'));

        // English stays English
        $this->withSession(['locale' => 'en'])->post('/admin/login', []);
        $this->assertStringContainsString('required', session('errors')->first('email'));
    }

    public function test_employee_form_errors_name_the_question_not_the_field_key(): void
    {
        $period = AssessmentPeriod::create(['title' => 'L', 'slug' => 'l10n-xyz']);
        $period->questions()->create(['question_en' => 'Q1', 'question_mm' => 'Q1', 'order_no' => 1]);
        $period->questions()->create(['question_en' => 'Q2', 'question_mm' => 'Q2', 'order_no' => 2]);

        $this->withSession(['locale' => 'mm'])->post("/assessment/{$period->slug}/submit", [])
            ->assertSessionHasErrors();
        $all = implode(' | ', session('errors')->all());
        $this->assertStringContainsString('မေးခွန်း 1', $all);
        $this->assertStringContainsString('မေးခွန်း 2', $all);
        $this->assertDoesNotMatchRegularExpression('/answers\.\d+/', $all);

        $this->withSession(['locale' => 'en'])->post("/assessment/{$period->slug}/submit", []);
        $this->assertStringContainsString('Question 1', implode(' | ', session('errors')->all()));
    }

    public function test_admin_pages_render_in_myanmar_without_raw_keys(): void
    {
        $admin = User::create(['name' => 'Ad', 'email' => 'l10n-ad@x.com', 'role' => 'admin', 'password' => 'secret123']);
        $admin->assignRole('admin');
        $this->actingAs($admin)->withSession(['locale' => 'mm']);

        foreach (['/admin', '/admin/assessment-periods', '/admin/assessments', '/admin/departments', '/admin/departments/create',
                  '/admin/positions', '/admin/users', '/admin/users/create', '/admin/roles', '/admin/roles/create',
                  '/admin/permissions', '/admin/permissions/create'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('lang="my"', $html, $url);
            $this->assertStringContainsString('ပင်မစာမျက်နှာ', $html, "$url breadcrumb");
            $this->assertDoesNotMatchRegularExpression('/\b(validation|pagination|auth|passwords)\.[a-z_]+\b/', strip_tags($html), "$url shows a raw translation key");
        }
    }
}
