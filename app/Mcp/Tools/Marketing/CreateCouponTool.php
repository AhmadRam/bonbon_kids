<?php

namespace App\Mcp\Tools\Marketing;

use App\Mcp\Tools\BaseAdminTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Webkul\CartRule\Repositories\CartRuleRepository;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;

#[Name('create_coupon')]
#[Description('Create a new discount coupon rule (percentage or fixed amount discount with promo code).')]
class CreateCouponTool extends BaseAdminTool
{
    public function handle(
        Request $request,
        CartRuleRepository $cartRuleRepository,
        ChannelRepository $channelRepository,
        CustomerGroupRepository $customerGroupRepository
    ): Response {
        if ($authError = $this->checkPermission('marketing.promotions.cart-rules.create')) {
            return $authError;
        }

        $code = strtoupper(trim((string) $request->get('coupon_code')));
        $discountAmount = (float) $request->get('discount_amount');
        $actionType = $request->get('discount_type', 'by_percent') === 'by_fixed' ? 'by_fixed' : 'by_percent';
        $name = $request->get('name', "Promo {$code}");
        $startsFrom = $request->get('starts_from');
        $endsTill = $request->get('ends_till');
        $usageLimit = (int) ($request->get('usage_limit', 0));

        if (! $code || $discountAmount <= 0) {
            return $this->errorResponse('Validation error: coupon_code and positive discount_amount are required.');
        }

        if ($actionType === 'by_percent' && $discountAmount > 100) {
            return $this->errorResponse('Percentage discount cannot exceed 100%.');
        }

        $channels = $channelRepository->all()->pluck('id')->toArray();
        $customerGroups = $customerGroupRepository->all()->pluck('id')->toArray();

        $cartRuleData = [
            'name' => $name,
            'description' => "Coupon code {$code} with {$discountAmount} ".($actionType === 'by_percent' ? '%' : 'fixed').' discount.',
            'starts_from' => $startsFrom,
            'ends_till' => $endsTill,
            'status' => 1,
            'coupon_type' => 1,
            'use_auto_generation' => 0,
            'coupon_code' => $code,
            'uses_per_coupon' => $usageLimit > 0 ? $usageLimit : null,
            'uses_per_customer' => 1,
            'action_type' => $actionType,
            'discount_amount' => $discountAmount,
            'discount_quantity' => 0,
            'discount_step' => 0,
            'apply_to_shipping' => 0,
            'free_shipping' => 0,
            'end_other_rules' => 0,
            'channels' => $channels,
            'customer_groups' => $customerGroups,
        ];

        try {
            $cartRule = $cartRuleRepository->create($cartRuleData);

            return $this->successResponse("Coupon [{$code}] created successfully.", [
                'rule_id' => $cartRule->id,
                'coupon_code' => $code,
                'discount' => $discountAmount.($actionType === 'by_percent' ? '%' : ' currency units'),
                'starts_from' => $startsFrom,
                'ends_till' => $endsTill,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse("Failed to create coupon: {$e->getMessage()}");
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'coupon_code' => $schema->string()->description('The coupon code customers will enter at checkout (e.g. SAVE20).')->required(),
            'discount_amount' => $schema->number()->description('The discount value (e.g. 20 for 20% or $20).')->required(),
            'discount_type' => $schema->string()->description('Discount calculation type: "by_percent" or "by_fixed". Defaults to by_percent.'),
            'name' => $schema->string()->description('Descriptive name for the promotion rule.'),
            'starts_from' => $schema->string()->description('Start date (YYYY-MM-DD format).'),
            'ends_till' => $schema->string()->description('Expiry date (YYYY-MM-DD format).'),
            'usage_limit' => $schema->integer()->description('Total number of times this coupon can be used across all customers.'),
        ];
    }
}
