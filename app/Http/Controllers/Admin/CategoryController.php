<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $categories = Category::where('company_id', $companyId)
            ->with('parent:id,name')
            ->withCount('products')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        $parentCategories = Category::where('company_id', $companyId)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $parentCategories = Category::where('company_id', $user->company_id)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['company_id'] = $user->company_id;
        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        // Ensure unique slug within company
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (Category::where('company_id', $user->company_id)->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug.'-'.$counter;
            $counter++;
        }

        $category = Category::create($validated);

        ActivityLog::log(
            ActivityLog::ACTION_CREATED,
            $user->company_id,
            $user->id,
            null,
            Category::class,
            $category->id,
            null,
            $category->toArray()
        );

        // Get updated categories list for product create/edit pages
        $categories = Category::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'parent_id']);

        return back()->with('success', 'Category created successfully.')
            ->with('created_category_id', $category->id)
            ->with('categories', $categories);
    }

    public function edit(Request $request, Category $category): Response
    {
        $this->authorizeAccess($category);

        $user = $request->user();
        $parentCategories = Category::where('company_id', $user->company_id)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorizeAccess($category);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$category->id])],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $category->toArray();

        // Update slug if name changed
        if ($validated['name'] !== $category->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (Category::where('company_id', $user->company_id)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $category->id)
                ->exists()) {
                $validated['slug'] = $baseSlug.'-'.$counter;
                $counter++;
            }
        }

        $category->update($validated);

        ActivityLog::log(
            ActivityLog::ACTION_UPDATED,
            $user->company_id,
            $user->id,
            null,
            Category::class,
            $category->id,
            $oldValues,
            $category->fresh()->toArray()
        );

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category)
    {
        $this->authorizeAccess($category);
        $user = $request->user();

        // Check if category has products
        if ($category->products()->exists()) {
            return back()->with('error', 'Cannot delete category with products. Please reassign products first.');
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        ActivityLog::log(
            ActivityLog::ACTION_DELETED,
            $user->company_id,
            $user->id,
            null,
            Category::class,
            $category->id,
            $category->toArray(),
            null
        );

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    protected function authorizeAccess(Category $category): void
    {
        if ($category->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
