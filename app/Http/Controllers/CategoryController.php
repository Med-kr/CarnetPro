<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Flatshare;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function store(StoreCategoryRequest $request, Flatshare $flatshare): RedirectResponse
    {
        Category::create([
            'flatshare_id' => $flatshare->id,
            'name' => $request->string('name')->toString(),
            'icon' => $request->string('icon')->toString(),
        ]);

        return back()->with('status', 'Category created.');
    }

    public function update(UpdateCategoryRequest $request, Flatshare $flatshare, Category $category): RedirectResponse
    {
        abort_if($category->flatshare_id !== $flatshare->id, 404);

        $category->update($request->validated());

        return back()->with('status', 'Category updated.');
    }

    public function destroy(Flatshare $flatshare, Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        abort_if($category->flatshare_id !== $flatshare->id, 404);

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }
}
