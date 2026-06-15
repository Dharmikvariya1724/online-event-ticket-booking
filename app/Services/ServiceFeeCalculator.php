<?php

namespace App\Services;

use App\Models\PromoCode;

class ServiceFeeCalculator
{
    /**
     * Resolve the configured service fee rate as a decimal.
     */
    public static function serviceFeeRate(): float
    {
        return max(0.0, ((float) config('ticketly.service_fee_percentage', 5)) / 100);
    }

    /**
     * Resolve the configured portal fee rate as a decimal.
     */
    public static function portalFeeRate(): float
    {
        return max(0.0, ((float) config('ticketly.portal_fee_percentage', 10)) / 100);
    }

    /**
     * Calculate the service fee for a given subtotal.
     */
    public static function fee(float $subtotal): float
    {
        return self::pricingWithRates($subtotal, 0.0, self::serviceFeeRate())['service_fee'];
    }

    /**
     * Calculate the portal fee for a given subtotal.
     */
    public static function portalFee(float $subtotal): float
    {
        return self::pricingWithRates($subtotal, self::portalFeeRate(), 0.0)['portal_fee'];
    }

    /**
     * Build pricing from explicit fee rates.
     *
     * @return array{subtotal: float, discount: float, gross_total: float, portal_fee: float, service_fee: float, total: float}
     */
    public static function pricingWithRates(
        float $subtotal,
        float $portalFeeRate,
        float $serviceFeeRate,
        float $discount = 0.0
    ): array {
        $subtotal = round(max(0.0, $subtotal), 2);
        $portalFeeRate = max(0.0, $portalFeeRate);
        $serviceFeeRate = max(0.0, $serviceFeeRate);

        $portalFee = round($subtotal * $portalFeeRate, 2);
        $serviceFee = round($subtotal * $serviceFeeRate, 2);
        $grossTotal = round($subtotal + $portalFee + $serviceFee, 2);
        $appliedDiscount = min(max(0.0, round($discount, 2)), $grossTotal);
        $total = round(max($grossTotal - $appliedDiscount, 0.0), 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $appliedDiscount,
            'gross_total' => $grossTotal,
            'portal_fee' => $portalFee,
            'service_fee' => $serviceFee,
            'total' => $total,
        ];
    }

    /**
     * Resolve pricing for a subtotal using the currently configured fee rates.
     *
     * Discount is treated as a monetary reduction against the total before discount.
     *
     * @return array{subtotal: float, discount: float, gross_total: float, portal_fee: float, service_fee: float, total: float}
     */
    public static function total(float $subtotal, float $discount = 0.0): array
    {
        return self::pricingWithRates(
            $subtotal,
            self::portalFeeRate(),
            self::serviceFeeRate(),
            $discount
        );
    }

    /**
     * Return the promo discount amount against the total before discount.
     *
     * @return float
     */
    public static function discountForPromo(float $subtotal, ?PromoCode $promo = null): float
    {
        if (!$promo) {
            return 0.0;
        }

        $basePricing = self::total($subtotal);

        return min(
            max(0.0, round($promo->calculateDiscount($basePricing['gross_total']), 2)),
            $basePricing['gross_total']
        );
    }

    /**
     * Return the pricing breakdown using a promo code.
     *
     * @return array{subtotal: float, discount: float, gross_total: float, portal_fee: float, service_fee: float, total: float}
     */
    public static function totalForPromo(float $subtotal, ?PromoCode $promo = null): array
    {
        return self::total($subtotal, self::discountForPromo($subtotal, $promo));
    }

    /**
     * Convert a stored discount amount into an effective rate of the pre-discount total.
     */
    public static function effectiveDiscountRate(float $grossTotal, float $discountAmount): float
    {
        $grossTotal = round(max(0.0, $grossTotal), 2);

        if ($grossTotal <= 0.0) {
            return 0.0;
        }

        return min(max($discountAmount / $grossTotal, 0.0), 1.0);
    }

    /**
     * Infer fee rates from stored fee amounts for a booking/reservation snapshot.
     *
     * @return array{portal_fee_rate: float, service_fee_rate: float}
     */
    public static function inferFeeRates(float $subtotal, float $portalFee, float $serviceFee): array
    {
        $subtotal = round(max(0.0, $subtotal), 2);

        if ($subtotal <= 0.0) {
            return [
                'portal_fee_rate' => 0.0,
                'service_fee_rate' => 0.0,
            ];
        }

        return [
            'portal_fee_rate' => max($portalFee / $subtotal, 0.0),
            'service_fee_rate' => max($serviceFee / $subtotal, 0.0),
        ];
    }

    public static function format(float $amount): string
    {
        return ticketly_money($amount);
    }

    public static function toPence(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public static function fromPence(int $pence): float
    {
        return round($pence / 100, 2);
    }
}
