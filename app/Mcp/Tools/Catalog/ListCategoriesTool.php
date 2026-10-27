<?php

namespace App\Mcp\Tools\Catalog;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Category\Repositories\CategoryRepository;

#[Name('list_categories')]
#[Description('List store categories with their hierarchical tree or flat list.')]
class ListCategoriesTool extends BaseAdminTool
{
    public function handle(Request $request, CategoryRepository $categoryRepository): Response
    {
        if ($authError = $this->checkPermission('catalog.categories')) {
            return $authError;
        }

        $categories = $categoryRepository->all();

        $list = $categories->map(fn ($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'status' => (bool) $cat->status,
            'parent_id' => $cat->parent_id,
            'position' => $cat->position,
        ]);

        return $this->successResponse('Categories retrieved successfully.', ['categories' => $list]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
