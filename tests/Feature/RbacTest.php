<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Runs in a rolled-back transaction, so it is safe against the dev database. */
class RbacTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed(RbacSeeder::class);
    }

    private function staff(string $role, string $tag = 'u'): User
    {
        $user = User::create(['name' => "T $role", 'email' => "$tag-$role-rbac@x.com", 'password' => 'secret123', 'role' => $role]);
        $user->assignRole($role);

        return $user;
    }

    // ---------- access control ----------

    public function test_seeded_roles_and_permissions_exist_and_admin_bypasses_everything(): void
    {
        $this->assertSame(10, Permission::whereIn('name', \App\Support\Rbac::permissionNames())->count());

        // A permission created later in the UI is still granted to admin via Gate::before.
        Permission::create(['name' => 'later.created', 'guard_name' => 'web']);
        $this->assertTrue($this->staff('admin')->can('later.created'));
        $this->assertFalse($this->staff('ceo')->can('later.created'));
    }

    public function test_ceo_keeps_oversight_pages_but_not_access_control(): void
    {
        $this->actingAs($this->staff('ceo'));

        foreach (['/admin', '/admin/assessments', '/admin/assessment-periods', '/admin/departments'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['/admin/users', '/admin/roles', '/admin/permissions', '/admin/users/create'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get('/admin')->assertDontSee('/admin/users', false);
    }

    public function test_custom_role_only_sees_and_reaches_what_it_was_given(): void
    {
        Role::create(['name' => 'Auditor', 'guard_name' => 'web'])->givePermissionTo('submissions.view');
        $user = User::create(['name' => 'Aud', 'email' => 'aud-rbac@x.com', 'password' => 'secret123', 'role' => 'Auditor']);
        $user->assignRole('Auditor');
        $this->actingAs($user);

        $this->get('/admin/assessments')->assertOk()->assertSee('Submissions')->assertDontSee('/admin/departments', false);
        $this->get('/admin')->assertForbidden();                    // no dashboard.view
        $this->get('/admin/assessment-periods')->assertForbidden();
        $this->get('/admin/assessments/export/csv')->assertForbidden(); // view != export
    }

    public function test_login_rules(): void
    {
        $emp = User::create(['name' => 'Emp', 'email' => 'emp-rbac@x.com', 'password' => 'secret123', 'role' => 'employee']);
        $this->post('/admin/login', ['email' => $emp->email, 'password' => 'secret123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->staff('gm');
        $this->post('/admin/login', ['email' => 'u-gm-rbac@x.com', 'password' => 'secret123'])->assertRedirect('/admin');
        $this->post('/admin/logout');

        // A role with zero permissions has nowhere to land, so it is turned away with a clear message.
        Role::create(['name' => 'Empty', 'guard_name' => 'web']);
        User::create(['name' => 'E2', 'email' => 'e2-rbac@x.com', 'password' => 'secret123', 'role' => 'Empty'])->assignRole('Empty');
        $this->post('/admin/login', ['email' => 'e2-rbac@x.com', 'password' => 'secret123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        // Auditor lands on a page they can actually open, not a 403 dashboard.
        Role::create(['name' => 'Auditor', 'guard_name' => 'web'])->givePermissionTo('submissions.view');
        User::create(['name' => 'A', 'email' => 'a-rbac@x.com', 'password' => 'secret123', 'role' => 'Auditor'])->assignRole('Auditor');
        $this->post('/admin/login', ['email' => 'a-rbac@x.com', 'password' => 'secret123'])->assertRedirect('/admin/assessments');
    }

    public function test_employee_form_refuses_staff_accounts(): void
    {
        $period = AssessmentPeriod::create(['title' => 'P', 'slug' => 'rbac-form-xyz']);
        $q = $period->questions()->create(['question_en' => 'Q', 'question_mm' => 'Q', 'order_no' => 1]);
        \App\Models\Department::create(['name_en' => 'ZZ RBAC Dept', 'name_mm' => 'x']);
        \App\Models\Position::create(['name_en' => 'ZZ RBAC Pos', 'name_mm' => 'x']);

        $boss = $this->staff('gm');
        $boss->update(['employee_id' => 'STAFF-1']);

        // Typing a staff member's ID + email into the public form must not attach to, change, or submit as that account.
        $before = $boss->only(['name', 'department']);
        $this->post("/assessment/{$period->slug}/submit", [
            'name' => 'Intruder', 'employee_id' => 'STAFF-1', 'email' => $boss->email,
            'position' => 'ZZ RBAC Pos', 'department' => 'ZZ RBAC Dept', 'answers' => [$q->id => 'a'],
        ])->assertSessionHasErrors('employee_id');

        $this->assertSame(0, Assessment::where('user_id', $boss->id)->count());
        $this->assertSame($before, $boss->fresh()->only(['name', 'department']));

        // ...also when the account's role was assigned only via Spatie (stale users.role column).
        $boss->forceFill(['role' => 'employee'])->saveQuietly();
        $this->post("/assessment/{$period->slug}/submit", [
            'name' => 'Intruder', 'employee_id' => 'STAFF-1', 'email' => $boss->email,
            'position' => 'ZZ RBAC Pos', 'department' => 'ZZ RBAC Dept', 'answers' => [$q->id => 'a'],
        ])->assertSessionHasErrors('employee_id');
        $this->assertSame(0, Assessment::where('user_id', $boss->id)->count());
    }

    // ---------- users ----------

    public function test_user_crud_and_role_column_sync(): void
    {
        $this->actingAs($this->staff('admin'));

        $this->get('/admin/users')->assertOk()->assertSee('aria-label="Breadcrumb"', false);
        $this->get('/admin/users/create')->assertOk();

        // staff needs email + password
        $this->post('/admin/users', ['name' => 'N', 'role' => 'gm'])->assertSessionHasErrors(['email', 'password']);
        $this->post('/admin/users', ['name' => 'N', 'email' => 'n-rbac@x.com', 'role' => 'gm', 'password' => 'short', 'password_confirmation' => 'short'])->assertSessionHasErrors('password');
        $this->post('/admin/users', ['name' => 'N', 'email' => 'n-rbac@x.com', 'role' => 'gm', 'password' => 'longenough1', 'password_confirmation' => 'nomatch'])->assertSessionHasErrors('password');

        $this->post('/admin/users', ['name' => 'New GM', 'email' => 'n-rbac@x.com', 'role' => 'gm', 'password' => 'longenough1', 'password_confirmation' => 'longenough1', 'department' => 'ZZ none'])
            ->assertSessionHasErrors('department'); // unknown department rejected

        $this->post('/admin/users', ['name' => 'New GM', 'email' => 'n-rbac@x.com', 'role' => 'gm', 'password' => 'longenough1', 'password_confirmation' => 'longenough1'])
            ->assertRedirect('/admin/users')->assertSessionHasNoErrors();
        $user = User::where('email', 'n-rbac@x.com')->firstOrFail();
        $this->assertTrue($user->hasRole('gm'));
        $this->assertSame('gm', $user->role);
        $this->assertNotSame('longenough1', $user->password); // hashed

        $this->get("/admin/users/{$user->id}")->assertOk()->assertSee('New GM')->assertSee('dashboard.view');
        $this->get("/admin/users/{$user->id}/edit")->assertOk();

        // edit: password optional, role change re-syncs the column, role removal demotes to employee
        $this->put("/admin/users/{$user->id}", ['name' => 'Renamed', 'email' => 'n-rbac@x.com', 'role' => 'ceo'])->assertSessionHasNoErrors();
        $this->assertSame('ceo', $user->fresh()->role);
        $oldHash = $user->fresh()->password;
        $this->put("/admin/users/{$user->id}", ['name' => 'Renamed', 'email' => 'n-rbac@x.com', 'role' => 'ceo', 'password' => '', 'password_confirmation' => ''])->assertSessionHasNoErrors();
        $this->assertSame($oldHash, $user->fresh()->password);   // blank password keeps the current one
        $this->put("/admin/users/{$user->id}", ['name' => 'Renamed', 'email' => 'n-rbac@x.com', 'role' => 'ceo', 'password' => 'brandnew-pass1', 'password_confirmation' => 'brandnew-pass1'])->assertSessionHasNoErrors();
        $this->assertNotSame($oldHash, $user->fresh()->password); // a new one replaces it
        $this->put("/admin/users/{$user->id}", ['name' => 'Renamed', 'email' => 'n-rbac@x.com', 'role' => ''])->assertSessionHasNoErrors();
        $this->assertSame('employee', $user->fresh()->role);
        $this->assertFalse($user->fresh()->isStaff());

        // employee created without a role needs neither email nor password
        $this->post('/admin/users', ['name' => 'Plain Emp', 'employee_id' => 'E-RBAC-1'])->assertSessionHasNoErrors();
        $this->assertSame('employee', User::where('employee_id', 'E-RBAC-1')->value('role'));

        $this->get('/admin/users?search=Renamed&role=employee')->assertOk()->assertSee('Renamed');
        $this->get('/admin/users?role=gm')->assertOk()->assertDontSee('Renamed');
        $this->get('/admin/users?sort=password;drop&dir=zz&search=%25')->assertOk();

        $this->delete("/admin/users/{$user->id}")->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_user_cascades_their_submissions(): void
    {
        $this->actingAs($this->staff('admin'));
        $period = AssessmentPeriod::create(['title' => 'P', 'slug' => 'rbac-p-xyz']);
        $emp = User::create(['name' => 'E', 'email' => 'cascade-rbac@x.com']);
        Assessment::create(['assessment_period_id' => $period->id, 'user_id' => $emp->id, 'status' => 'submitted', 'submitted_at' => now()]);

        $this->delete("/admin/users/{$emp->id}")->assertRedirect();
        $this->assertSame(0, Assessment::where('user_id', $emp->id)->count());
    }

    public function test_lockout_and_escalation_guards(): void
    {
        $admin = $this->staff('admin', 'a1');
        $this->actingAs($admin);

        // cannot delete yourself / change your own role
        $this->delete("/admin/users/{$admin->id}")->assertSessionHasErrors('delete');
        $this->put("/admin/users/{$admin->id}", ['name' => 'x', 'email' => $admin->email, 'role' => 'gm'])->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->hasRole('admin'));

        // last admin cannot be demoted or deleted by someone else
        User::role('admin')->whereKeyNot($admin->id)->each(fn ($u) => $u->syncRoles([]));
        $second = $this->staff('admin', 'a2');
        $this->actingAs($second)->put("/admin/users/{$admin->id}", ['name' => 'x', 'email' => $admin->email, 'role' => 'gm'])->assertSessionHasNoErrors(); // 2 admins -> allowed
        $this->assertFalse($admin->fresh()->hasRole('admin'));
        $this->actingAs($this->staff('admin', 'a3'));
        $other = User::role('admin')->where('id', '!=', auth()->id())->first();
        $other->syncRoles([]);        // leave a3 as the only admin
        $this->put("/admin/users/".auth()->id(), ['name' => 'x', 'email' => auth()->user()->email, 'role' => 'gm'])->assertSessionHasErrors('role'); // self + last admin

        // a user manager who is not an admin cannot create admins, nor touch admin accounts
        Role::create(['name' => 'HR', 'guard_name' => 'web'])->givePermissionTo(['users.manage', 'roles.manage']);
        $hr = User::create(['name' => 'HR', 'email' => 'hr-rbac@x.com', 'password' => 'secret123', 'role' => 'HR']);
        $hr->assignRole('HR');
        $victim = $this->staff('admin', 'v1');
        $this->actingAs($hr);
        $this->post('/admin/users', ['name' => 'Evil', 'email' => 'evil-rbac@x.com', 'password' => 'longenough1', 'password_confirmation' => 'longenough1', 'role' => 'admin'])->assertSessionHasErrors('role');
        $this->get("/admin/users/{$victim->id}/edit")->assertForbidden();
        $this->put("/admin/users/{$victim->id}", ['name' => 'x', 'email' => $victim->email, 'role' => ''])->assertForbidden();
        $this->delete("/admin/users/{$victim->id}")->assertForbidden();
        $this->get('/admin/roles/' . Role::findByName('admin')->id . '/edit')->assertForbidden();
        $this->assertTrue($victim->fresh()->hasRole('admin'));
    }

    // ---------- roles ----------

    public function test_role_crud_with_permission_checkboxes(): void
    {
        $this->actingAs($this->staff('admin'));

        $this->get('/admin/roles')->assertOk()->assertSee('Built-in');
        $this->get('/admin/roles/create')->assertOk()->assertSee('submissions.view');

        $this->post('/admin/roles', ['name' => 'Reviewer', 'permissions' => ['submissions.view', 'dashboard.view']])->assertRedirect('/admin/roles');
        $role = Role::findByName('Reviewer');
        $this->assertEqualsCanonicalizing(['submissions.view', 'dashboard.view'], $role->permissions->pluck('name')->all());

        $this->post('/admin/roles', ['name' => 'Reviewer'])->assertSessionHasErrors('name');                 // duplicate
        $this->post('/admin/roles', ['name' => 'Bad<script>'])->assertSessionHasErrors('name');                // charset
        $this->post('/admin/roles', ['name' => 'X', 'permissions' => ['nope.nope']])->assertSessionHasErrors('permissions.*');

        $this->get("/admin/roles/{$role->id}/edit")->assertOk();
        $this->put("/admin/roles/{$role->id}", ['name' => 'Reviewer 2', 'permissions' => ['submissions.view']])->assertRedirect('/admin/roles');
        $role->refresh();
        $this->assertSame('Reviewer 2', $role->name);
        $this->assertSame(['submissions.view'], $role->permissions->pluck('name')->all());

        // renaming propagates to users.role
        $u = User::create(['name' => 'RR', 'email' => 'rr-rbac@x.com', 'password' => 'secret123', 'role' => 'Reviewer 2']);
        $u->assignRole('Reviewer 2');
        $this->put("/admin/roles/{$role->id}", ['name' => 'Reviewer 3', 'permissions' => ['submissions.view']]);
        $this->assertSame('Reviewer 3', $u->fresh()->role);

        // in-use role cannot be deleted; empty one can
        $this->delete("/admin/roles/{$role->id}")->assertSessionHasErrors('delete');
        $u->syncRoles([]);
        $this->delete("/admin/roles/{$role->id}")->assertRedirect('/admin/roles');
        $this->assertNull(Role::where('name', 'Reviewer 3')->first());

        // built-ins: not deletable, not renamable, admin not editable at all
        $ceo = Role::findByName('ceo');
        $this->delete("/admin/roles/{$ceo->id}")->assertSessionHasErrors('delete');
        $this->put("/admin/roles/{$ceo->id}", ['name' => 'renamed', 'permissions' => ['dashboard.view']]);
        $this->assertSame('ceo', $ceo->fresh()->name);
        $adminRole = Role::findByName('admin');
        $this->put("/admin/roles/{$adminRole->id}", ['permissions' => []])->assertForbidden();
        $this->delete("/admin/roles/{$adminRole->id}")->assertSessionHasErrors('delete');
        $this->assertSame(10, $adminRole->fresh()->permissions->count());
    }

    // ---------- permissions ----------

    public function test_permission_crud(): void
    {
        $this->actingAs($this->staff('admin'));

        $this->get('/admin/permissions')->assertOk()->assertSee('dashboard.view')->assertSee('Locked');
        $this->get('/admin/permissions/create')->assertOk();

        $this->post('/admin/permissions', ['name' => 'reports.view', 'guard_name' => 'web'])->assertRedirect('/admin/permissions');
        $perm = Permission::findByName('reports.view');
        $this->post('/admin/permissions', ['name' => 'reports.view', 'guard_name' => 'web'])->assertSessionHasErrors('name');
        $this->post('/admin/permissions', ['name' => 'Has Spaces', 'guard_name' => 'web'])->assertSessionHasErrors('name');
        $this->post('/admin/permissions', ['name' => 'ok.name', 'guard_name' => 'api'])->assertSessionHasErrors('guard_name');

        $this->get("/admin/permissions/{$perm->id}/edit")->assertOk();
        $this->put("/admin/permissions/{$perm->id}", ['name' => 'reports.read', 'guard_name' => 'web'])->assertRedirect('/admin/permissions');
        $this->assertSame('reports.read', $perm->fresh()->name);

        // custom permission shows up on the role form
        $this->get('/admin/roles/create')->assertSee('reports.read')->assertSee('Custom');

        $this->delete("/admin/permissions/{$perm->id}")->assertRedirect('/admin/permissions');
        $this->assertNull(Permission::where('name', 'reports.read')->first());

        // built-ins are protected
        $builtin = Permission::findByName('dashboard.view');
        $this->delete("/admin/permissions/{$builtin->id}")->assertSessionHasErrors('delete');
        $this->put("/admin/permissions/{$builtin->id}", ['name' => 'hacked', 'guard_name' => 'web'])->assertForbidden();
        $this->assertSame('dashboard.view', $builtin->fresh()->name);
    }
}
