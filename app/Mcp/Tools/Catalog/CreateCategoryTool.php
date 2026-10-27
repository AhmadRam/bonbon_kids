<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Category\Repositories\CategoryRepository;

#[Name('create_category')]
#[Description('Create a new product category in the store catalog.')]
class CreateCategoryTool extends BaseAdminTool
{
    public function handle(Request $request, CategoryRepository $categoryRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.categories.create')) {
            return $authError;
        }

        $name = trim((string) $request->get('name'));
        $slug = $request->get('slug') ? Str::slug($request->get('slug')) : Str::slug($name);
        $parentId = $request->get('parent_id');
        $description = $request->get('description', $name);

        if (! $name) {
            return $this->errorResponse('Category name is required.');
        }

        if (! $parentId) {
            $root = $categoryRepository->getRootCategories()->first();
            $parentId = $root ? $root->id : 1;
        }

        $category = $categoryRepository->create([
            'parent_id' => $parentId,
            'position' => 1,
            'status' => 1,
            'display_mode' => 'products_and_description',
            'en' => [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'meta_title' => $name,
                'meta_description' => $description,
            ],
        ]);

        return $this->successResponse('Category created successfully.', [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->parent_id,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Category name.')->required(),
            'slug' => $schema->string()->description('URL friendly slug. Defaults to slugified name.'),
            'parent_id' => $schema->integer()->description('Parent category ID. If omitted, uses root category.'),
            'description' => $schema->string()->description('Category description.'),
        ];
    }
}
