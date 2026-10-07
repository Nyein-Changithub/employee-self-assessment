<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private const SORTABLE = ['id', 'name', 'created_at'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'id';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $permissions = Permission::query()
            ->withCount('roles')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.permissions.index', compact('permissions', 'search', 'perPage', 'sort', 'dir'));
    }

    public function create(): View
    {
        return view('admin.permissions.create', ['permission' => null, 'guards' => $this->guards()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Permission::create($this->validated($request));

        return redirect()->route('admin.permissions.index')->with('status', __('Added.'));
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', [
            'permission' => $permission,
            'guards' => $this->guards(),
            'builtIn' => Rbac::isBuiltInPermission($permission->name),
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        // Built-in names are referenced by routes; only custom permissions can be edited.
        abort_if(Rbac::isBuiltInPermission($permission->name), 403);

        $permission->update($this->validated($request, $permission));

        return redirect()->route('admin.permissions.index')->with('status', __('Saved.'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if (Rbac::isBuiltInPermission($permission->name)) {
            return back()->withErrors(['delete' => __('Built-in permissions cannot be deleted.')]);
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('status', __('Deleted.'));
    }

    private function guards(): array
    {
        return array_keys(config('auth.guards'));
    }

    private function validated(Request $request, ?Permission $permission = null): array
    {
        $data = $request->validate([
            'guard_name' => ['required', Rule::in($this->guards())],
            'name' => [
                'required', 'string', 'max:100', 'regex:/^[a-z0-9._\-]+$/',
                Rule::unique('permissions', 'name')->where('guard_name', $request->input('guard_name'))->ignore($permission?->id),
            ],
        ], [
            'name.regex' => __('Use lowercase letters, numbers, dots, dashes or underscores only.'),
        ]);

        return $data;
    }
}
