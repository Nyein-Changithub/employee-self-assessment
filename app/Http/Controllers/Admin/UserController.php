<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const SORTABLE = ['id', 'name', 'created_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', '');
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'id';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $users = User::query()
            ->with('roles:id,name')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('employee_id', 'like', $like));
            })
            ->when($roleFilter === 'employee', fn ($q) => $q->doesntHave('roles'))
            ->when($roleFilter !== '' && $roleFilter !== 'employee', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $roleFilter)))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
            'search' => $search, 'roleFilter' => $roleFilter, 'perPage' => $perPage, 'sort' => $sort, 'dir' => $dir,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->authorizeRole($request, $data['role'] ?? null);

        $user = User::create($this->attributes($data));
        $this->applyRole($user, $data['role'] ?? null);

        return redirect()->route('admin.users.index')->with('status', __('Added.'));
    }

    public function show(User $user): View
    {
        $user->load('roles.permissions');

        return view('admin.users.show', [
            'user' => $user,
            'submissions' => $user->assessments()->where('status', 'submitted')->count(),
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
        ]);
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeTarget($request, $user);

        return view('admin.users.edit', $this->formData() + ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);

        $data = $this->validated($request, $user);
        $newRole = $data['role'] ?? null;

        if ($user->is($request->user()) && $newRole !== $user->roles->first()?->name) {
            throw ValidationException::withMessages(['role' => __('You cannot change your own role.')]);
        }
        if ($user->hasRole(Rbac::SUPER_ROLE) && $newRole !== Rbac::SUPER_ROLE) {
            $this->ensureNotLastAdmin($user);
        }
        $this->authorizeRole($request, $newRole);

        $user->update($this->attributes($data, $user));
        $this->applyRole($user, $newRole);

        return redirect()->route('admin.users.index')->with('status', __('Saved.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);

        if ($user->is($request->user())) {
            return back()->withErrors(['delete' => __('You cannot delete your own account.')]);
        }
        if ($user->hasRole(Rbac::SUPER_ROLE)) {
            $this->ensureNotLastAdmin($user);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('Deleted.'));
    }

    private function formData(): array
    {
        return [
            'roles' => Role::orderBy('name')->pluck('name'),
            'departments' => Department::orderBy('name_en')->get(),
            'positions' => Position::orderBy('name_en')->get(),
        ];
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $hasRole = filled($request->input('role'));
        // A password is required whenever the account can sign in and has none yet.
        $needsPassword = $hasRole && ! $user?->password;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [$hasRole ? 'required' : 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'employee_id' => ['nullable', 'string', 'max:100', Rule::unique('users', 'employee_id')->ignore($user?->id)],
            'password' => [$needsPassword ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'position' => ['nullable', Rule::exists('positions', 'name_en')],
            'department' => ['nullable', Rule::exists('departments', 'name_en')],
            'role' => ['nullable', Rule::exists('roles', 'name')],
        ]);
    }

    private function attributes(array $data, ?User $user = null): array
    {
        $attrs = collect($data)->only(['name', 'email', 'employee_id', 'position', 'department'])->map(fn ($v) => $v === '' ? null : $v)->all();

        if (filled($data['password'] ?? null)) {
            $attrs['password'] = $data['password'];
        }

        return $attrs;
    }

    private function applyRole(User $user, ?string $role): void
    {
        $role ? $user->syncRoles([$role]) : $user->syncRoles([]);
        $user->unsetRelation('roles');
        $user->syncRoleColumn();
    }

    /** Only an admin may hand out the admin role (otherwise users.manage would be a privilege-escalation path). */
    private function authorizeRole(Request $request, ?string $role): void
    {
        if ($role === Rbac::SUPER_ROLE && ! $request->user()->hasRole(Rbac::SUPER_ROLE)) {
            throw ValidationException::withMessages(['role' => __('Only an administrator can assign the admin role.')]);
        }
    }

    /** Non-admins may not edit or delete admin accounts. */
    private function authorizeTarget(Request $request, User $user): void
    {
        abort_if($user->hasRole(Rbac::SUPER_ROLE) && ! $request->user()->hasRole(Rbac::SUPER_ROLE), 403);
    }

    private function ensureNotLastAdmin(User $user): void
    {
        $others = User::role(Rbac::SUPER_ROLE)->whereKeyNot($user->id)->count();

        if ($others === 0) {
            throw ValidationException::withMessages(['role' => __('At least one administrator must remain.')]);
        }
    }
}
