<?php

namespace App\Mcp\Tools\Customers;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\Product\Repositories\ProductReviewRepository;

#[Name('list_reviews')]
#[Description('List customer product reviews with ratings, comments, and approval status.')]
class ListReviewsTool extends BaseAdminTool
{
    public function handle(Request $request, ProductReviewRepository $productReviewRepository): Response
    {
        if ($authError = $this->checkPermission('customers.reviews')) {
            return $authError;
        }

        $status = $request->get('status');
        $limit = (int) ($request->get('limit', 20));

        $query = $productReviewRepository->getModel()->newQuery()
            ->with(['product', 'customer']);

        if ($status) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate(min($limit, 50));

        $reviews = collect($paginator->items())->map(fn ($r) => [
            'id' => $r->id,
            'title' => $r->title,
            'rating' => (int) $r->rating,
            'comment' => $r->comment,
            'status' => $r->status,
            'customer_name' => $r->name ?: ($r->customer ? "{$r->customer->first_name} {$r->customer->last_name}" : 'Guest'),
            'product_name' => $r->product?->name,
            'product_sku' => $r->product?->sku,
            'created_at' => $r->created_at?->toIso8601String(),
        ]);

        return $this->successResponse('Reviews retrieved successfully.', [
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()->description('Filter by review status: approved, pending, or disapproved.'),
            'limit' => $schema->integer()->description('Max reviews to return (default 20, max 50).'),
            'page' => $schema->integer()->description('Pagination page number.'),
        ];
    }
}
