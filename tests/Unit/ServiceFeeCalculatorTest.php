<?php

namespace Tests\Unit;

use App\Models\PromoCode;
use App\Services\ServiceFeeCalculator;
use Tests\TestCase;

class ServiceFeeCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ticketly.portal_fee_percentage' => 10,
            'ticketly.service_fee_percentage' => 5,
        ]);
    }

    public function test_total_without_promo_is_ticket_total_plus_fees(): void
    {
        $pricing = ServiceFeeCalculator::total(100);

        $this->assertSame(100.0, $pricing['subtotal']);
        $this->assertSame(10.0, $pricing['portal_fee']);
        $this->assertSame(5.0, $pricing['service_fee']);
        $this->assertSame(115.0, $pricing['gross_total']);
        $this->assertSame(0.0, $pricing['discount']);
        $this->assertSame(115.0, $pricing['total']);
    }

    public function test_total_for_percentage_promo_applies_discount_after_fees(): void
    {
        $promo = new PromoCode([
            'type' => 'percentage',
            'value' => 20,
        ]);

        $pricing = ServiceFeeCalculator::totalForPromo(100, $promo);

        $this->assertSame(115.0, $pricing['gross_total']);
        $this->assertSame(23.0, $pricing['discount']);
        $this->assertSame(92.0, $pricing['total']);
    }

    public function test_total_for_fixed_promo_applies_discount_after_fees(): void
    {
        $promo = new PromoCode([
            'type' => 'fixed',
            'value' => 20,
        ]);

        $pricing = ServiceFeeCalculator::totalForPromo(100, $promo);

        $this->assertSame(115.0, $pricing['gross_total']);
        $this->assertSame(20.0, $pricing['discount']);
        $this->assertSame(95.0, $pricing['total']);
    }
}
