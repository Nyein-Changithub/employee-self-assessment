<?php

namespace Tests\Feature;

use App\Models\AssessmentCycle;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Runs inside a transaction that is rolled back, so it is safe against the dev database.
 */
class AssessmentFlowTest extends TestCase
{
    use DatabaseTransactions;

    private AssessmentCycle $cycle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->cycle = AssessmentCycle::create(['title' => 'T', 'slug' => 'test-cycle-xyz']);
        $this->cycle->questions()->create(['question_en' => 'Q1', 'question_mm' => 'Q1mm', 'order_no' => 1]);
        Department::create(['name_en' => 'ZZ Test Dept', 'name_mm' => 'ZZ-dept-mm']);
        Position::create(['name_en' => 'ZZ Test Pos', 'name_mm' => 'ZZ-pos-mm']);
    }

    private function payload(array $override = []): array
    {
        $q = $this->cycle->questions()->first();

        return array_merge([
            'name' => 'Aung Aung',
            'employee_id' => 'TEST-9001',
            'email' => '',
            'position' => 'ZZ Test Pos',
            'department' => 'ZZ Test Dept',
            'answers' => [$q->id => 'My answer'],
        ], $override);
    }

    private function submit(array $override = [])
    {
        return $this->post("/assessment/{$this->cycle->slug}/submit", $this->payload($override));
    }

    public function test_form_renders_dropdowns_in_both_languages(): void
    {
        $this->get("/assessment/{$this->cycle->slug}")
            ->assertOk()->assertSee('<option value="ZZ Test Dept"', false)->assertSee('ZZ Test Pos');

        $this->withSession(['locale' => 'mm'])->get("/assessment/{$this->cycle->slug}")
            ->assertOk()->assertSee('ZZ-dept-mm')->assertSee('ZZ-pos-mm');
    }

    public function test_email_is_optional_and_submission_is_locked(): void
    {
        $this->submit()->assertRedirect("/assessment/{$this->cycle->slug}")->assertSessionHas('just_submitted');

        $user = User::where('employee_id', 'TEST-9001')->firstOrFail();
        $this->assertNull($user->email);
        $this->assertSame('ZZ Test Dept', $user->department);
        $this->assertSame(1, $user->assessments()->count());

        $this->get("/assessment/{$this->cycle->slug}")->assertOk()->assertSee('My answer');
    }

    public function test_duplicate_submission_shows_banner_only_when_name_matches(): void
    {
        $this->submit()->assertSessionHasNoErrors();

        // A different browser session (no lock) re-submitting with the right name
        $this->flushSession();
        $this->submit()->assertSessionHas('already_submitted');
        $this->assertSame(1, User::where('employee_id', 'TEST-9001')->first()->assessments()->count());

        // Someone who only knows the ID must not unlock the answers
        $this->flushSession();
        $this->submit(['name' => 'Someone Else'])->assertSessionHasErrors('employee_id');
        $this->assertNull(session('submitted_assessment_id'));
        $this->assertSame('Aung Aung', User::where('employee_id', 'TEST-9001')->first()->name);
    }

    public function test_email_on_record_must_be_supplied_again(): void
    {
        $this->submit(['email' => 'a@x.com'])->assertSessionHasNoErrors();
        $this->flushSession();
        $this->submit(['email' => ''])->assertSessionHasErrors('employee_id');
        $this->submit(['email' => 'a@x.com'])->assertSessionHas('already_submitted');
    }

    public function test_unknown_department_or_position_is_rejected(): void
    {
        $this->submit(['department' => 'Made Up'])->assertSessionHasErrors('department');
        $this->submit(['position' => 'Made Up'])->assertSessionHasErrors('position');
    }

    public function test_admin_cannot_be_used_on_employee_form(): void
    {
        User::create(['name' => 'Boss', 'employee_id' => 'TEST-ADM', 'email' => 'boss@x.com', 'role' => 'ceo', 'password' => 'secret123']);
        $this->submit(['employee_id' => 'TEST-ADM', 'email' => 'boss@x.com'])->assertSessionHasErrors('employee_id');
    }

    private function admin(): User
    {
        $admin = User::create(['name' => 'Ad', 'email' => 'ad-test@x.com', 'role' => 'admin', 'password' => 'secret123']);
        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_pages_render_with_sidebar(): void
    {
        $this->admin();
        $q = $this->cycle->questions()->first();

        foreach (['/admin', '/admin/cycles', "/admin/cycles/{$this->cycle->id}/questions", '/admin/assessments',
                  '/admin/departments', '/admin/departments/create', '/admin/positions', '/admin/positions/create'] as $url) {
            $this->get($url)->assertOk()->assertSee('id="sidebar"', false)->assertSee('Master Data');
        }
    }

    public function test_guests_and_non_admins_cannot_reach_admin(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::create(['name' => 'E', 'email' => 'emp-test@x.com']))->get('/admin/departments')->assertForbidden();
    }

    public function test_lookup_crud_pages_and_rename_updates_users(): void
    {
        $this->admin();

        $this->post('/admin/departments', ['name_en' => 'Legal', 'name_mm' => 'ဥပဒေ', 'is_active' => 1])
            ->assertRedirect('/admin/departments')->assertSessionHasNoErrors();
        $dept = Department::where('name_en', 'Legal')->firstOrFail();
        $this->post('/admin/departments', ['name_en' => 'Legal', 'name_mm' => 'x'])->assertSessionHasErrors('name_en');

        $this->get("/admin/departments/{$dept->id}")->assertOk()->assertSee('Legal');
        $this->get("/admin/departments/{$dept->id}/edit")->assertOk()->assertSee('value="Legal"', false);

        User::create(['name' => 'E', 'email' => 'e-test@x.com', 'department' => 'Legal']);
        $this->put("/admin/departments/{$dept->id}", ['name_en' => 'Legal & Compliance', 'name_mm' => 'ဥပဒေ'])
            ->assertRedirect('/admin/departments')->assertSessionHasNoErrors();
        $this->assertSame('Legal & Compliance', User::where('email', 'e-test@x.com')->value('department'));
        // unchecked checkbox => inactive
        $this->assertFalse($dept->fresh()->is_active);

        $this->delete("/admin/departments/{$dept->id}")->assertRedirect('/admin/departments')->assertSessionHas('status');
        $this->assertDatabaseMissing('departments', ['id' => $dept->id]);
    }

    public function test_lookup_index_search_filter_sort_and_pagination(): void
    {
        $this->admin();
        Position::create(['name_en' => 'Zeta Lead', 'name_mm' => 'ဇီတာ', 'is_active' => false]);

        $this->get('/admin/positions?search=zeta')->assertOk()->assertSee('Zeta Lead')->assertDontSee('ZZ Test Pos');
        $this->get('/admin/positions?search=ဇီတာ')->assertOk()->assertSee('Zeta Lead');
        $this->get('/admin/positions?status=inactive')->assertOk()->assertSee('Zeta Lead')->assertDontSee('ZZ Test Pos');
        $this->get('/admin/positions?status=active&search=Zeta')->assertOk()->assertSee('No results found.');
        $this->get('/admin/positions?sort=name_en&dir=desc&per_page=10')->assertOk();
        // hostile sort / wildcard input must not break the query
        $this->get('/admin/positions?sort=password;drop&dir=x&search=%25')->assertOk()->assertSee('No results found.');
    }
}
