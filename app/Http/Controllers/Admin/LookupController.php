<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Master-data CRUD for the Department and Position lists used by the employee form.
 * The route's `type` default ("departments" | "positions") selects the model.
 */
class LookupController extends Controller
{
    private const TYPES = [
        'departments' => [
            'model' => Department::class,
            'user_column' => 'department',
            'labels' => [
                'plural' => 'Departments', 'singular' => 'Department', 'add' => 'Add Department',
                'create' => 'Create Department', 'edit' => 'Edit Department', 'details' => 'Department Details',
            ],
            'placeholder_en' => 'e.g. Human Resources',
            'placeholder_mm' => 'ဥပမာ - လူ့စွမ်းအားအရင်းအမြစ်',
        ],
        'positions' => [
            'model' => Position::class,
            'user_column' => 'position',
            'labels' => [
                'plural' => 'Positions', 'singular' => 'Position', 'add' => 'Add Position',
                'create' => 'Create Position', 'edit' => 'Edit Position', 'details' => 'Position Details',
            ],
            'placeholder_en' => 'e.g. Senior Manager',
            'placeholder_mm' => 'ဥပမာ - အကြီးတန်းမန်နေဂျာ',
        ],
    ];

    private const SORTABLE = ['id', 'name_en', 'created_at'];

    public function index(Request $request): View
    {
        $type = $this->type($request);
        $model = self::TYPES[$type]['model'];

        $search = trim((string) $request->query('search', ''));
        $status = in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null;
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50], true) ? (int) $request->query('per_page') : 10;
        $sort = in_array($request->query('sort'), self::SORTABLE, true) ? $request->query('sort') : 'id';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $items = $model::query()
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name_en', 'like', $like)->orWhere('name_mm', 'like', $like));
            })
            ->when($status, fn ($q, $s) => $q->where('is_active', $s === 'active'))
            ->orderBy($sort, $dir)
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.lookups.index', $this->meta($type) + compact('items', 'search', 'status', 'perPage', 'sort', 'dir'));
    }

    public function create(Request $request): View
    {
        return view('admin.lookups.create', $this->meta($this->type($request)));
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $this->type($request);
        self::TYPES[$type]['model']::create($this->validated($request, $type));

        return redirect()->route("admin.{$type}.index")->with('status', __('Added.'));
    }

    public function show(Request $request, int $id): View
    {
        $type = $this->type($request);
        $item = self::TYPES[$type]['model']::findOrFail($id);

        return view('admin.lookups.show', $this->meta($type) + [
            'item' => $item,
            'usage' => User::where(self::TYPES[$type]['user_column'], $item->name_en)->count(),
        ]);
    }

    public function edit(Request $request, int $id): View
    {
        $type = $this->type($request);

        return view('admin.lookups.edit', $this->meta($type) + [
            'item' => self::TYPES[$type]['model']::findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $type = $this->type($request);
        $item = self::TYPES[$type]['model']::findOrFail($id);
        $oldName = $item->name_en;

        $item->update($this->validated($request, $type, $id));

        // Users store the English name as text, so keep them in step with a rename.
        if ($oldName !== $item->name_en) {
            User::where(self::TYPES[$type]['user_column'], $oldName)
                ->update([self::TYPES[$type]['user_column'] => $item->name_en]);
        }

        return redirect()->route("admin.{$type}.index")->with('status', __('Saved.'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $type = $this->type($request);
        self::TYPES[$type]['model']::findOrFail($id)->delete();

        return redirect()->route("admin.{$type}.index")->with('status', __('Deleted.'));
    }

    private function type(Request $request): string
    {
        $type = $request->route('type');
        abort_unless(isset(self::TYPES[$type]), 404);

        return $type;
    }

    private function meta(string $type): array
    {
        return ['type' => $type, 'labels' => self::TYPES[$type]['labels'], 'config' => self::TYPES[$type]];
    }

    private function validated(Request $request, string $type, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name_en' => ['required', 'string', 'max:255', Rule::unique($type, 'name_en')->ignore($ignoreId)],
            'name_mm' => ['required', 'string', 'max:255'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
