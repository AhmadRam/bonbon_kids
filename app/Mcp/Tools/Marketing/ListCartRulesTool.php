<?php

namespace App\Mcp\Tools\Marketing;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\CartRule\Repositories\CartRuleRepository;

#[Name('list_cart_rules')]
#[Description('List promotional cart rules and discount coupons currently configured in the store.')]
class ListCartRulesTool extends BaseAdminTool
{
    public function handle(Request $request, CartRuleRepository $cartRuleRepository): Response
    {
        if ($authError = $this->checkPermission('marketing.promotions.cart-rules')) {
            return $authError;
        }

        $rules = $cartRuleRepository->with(['coupons'])->all();

        $list = $rules->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'status' => (bool) $r->status,
            'coupon_code' => $r->coupons->first()?->code,
            'action_type' => $r->action_type,
            'discount_amount' => (float) $r->discount_amount,
            'starts_from' => $r->starts_from,
            'ends_till' => $r->ends_till,
            'times_used' => (int) $r->times_used,
        ]);

        return $this->successResponse('Cart promotion rules retrieved successfully.', ['rules' => $list]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
