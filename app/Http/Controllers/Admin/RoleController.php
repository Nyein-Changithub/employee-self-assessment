<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const SORTABLE = ['id', 'name', 'created_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'id';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.roles.index', compact('roles', 'search', 'perPage', 'sort', 'dir'));
    }

    public function create(): View
    {
        return view('admin.roles.create', ['groups' => $this->permissionGroups(), 'role' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('status', __('Added.'));
    }

    public function edit(Request $request, Role $role): View
    {
        $this->authorizeRole($request, $role);

        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'groups' => $this->permissionGroups(),
            'locked' => $role->name === Rbac::SUPER_ROLE,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRole($request, $role);
        abort_if($role->name === Rbac::SUPER_ROLE, 403); // admin always holds everything

        $data = $this->validated($request, $role);

        // Built-in role names are referenced by code and cannot be renamed.
        if (! Rbac::isBuiltInRole($role->name)) {
            $role->update(['name' => $data['name']]);
        }
        $role->syncPermissions($data['permissions'] ?? []);
        $this->syncMemberColumns($role);

        return redirect()->route('admin.roles.index')->with('status', __('Saved.'));
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRole($request, $role);

        if (Rbac::isBuiltInRole($role->name)) {
            return back()->withErrors(['delete' => __('Built-in roles cannot be deleted.')]);
        }
        if ($role->users()->exists()) {
            return back()->withErrors(['delete' => __('This role is assigned to users. Reassign them first.')]);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', __('Deleted.'));
    }

    /** Permissions grouped for the checkbox grid; custom permissions land in their own group. */
    private function permissionGroups(): array
    {
        return Permission::orderBy('name')->get()
            ->groupBy(fn ($p) => Rbac::PERMISSIONS[$p->name] ?? 'Custom')
            ->sortKeys()->all();
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $builtIn = $role && Rbac::isBuiltInRole($role->name);

        return $request->validate([
            'name' => $builtIn ? ['nullable'] : [
                'required', 'string', 'max:100', 'regex:/^[A-Za-z0-9 _.\-]+$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ]);
    }

    /** Only an admin may touch the admin role. */
    private function authorizeRole(Request $request, Role $role): void
    {
        abort_if($role->name === Rbac::SUPER_ROLE && ! $request->user()->hasRole(Rbac::SUPER_ROLE), 403);
    }

    private function syncMemberColumns(Role $role): void
    {
        $role->users->each->syncRoleColumn();
    }
}
