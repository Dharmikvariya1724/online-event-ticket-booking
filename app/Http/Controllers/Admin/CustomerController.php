<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($request->status === 'active') {
                $query->where('is_suspended', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(int $id)
    {
        $customer = Customer::findOrFail($id);

        $bookings = Booking::with(['event', 'items.ticketTier'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.customers.show', compact('customer', 'bookings'));
    }

    public function suspend(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $customer->update([
            'is_suspended' => true,
            'suspended_at' => now(),
        ]);

        return back()->with('success', 'Customer suspended successfully.');
    }

    public function activate(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);

        $customer->update([
            'is_suspended' => false,
            'suspended_at' => null,
        ]);

        return back()->with('success', 'Customer reactivated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted.');
    }
}
