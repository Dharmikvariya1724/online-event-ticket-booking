@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-subtitle', 'Manage customer accounts and purchase history')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
  <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone"
      class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Statuses</option>
      <option value="active" @selected(request('status')==='active')>Active</option>
      <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
    </select>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Filter</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-gray-400 text-xs uppercase">
        <tr>
          <th class="text-left py-3">Customer</th>
          <th class="text-left py-3">Email</th>
          <th class="text-left py-3">Phone</th>
          <th class="text-left py-3">Status</th>
          <th class="text-right py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($customers as $customer)
        <tr>
          <td class="py-3 text-white font-semibold">{{ $customer->name ?? 'Guest' }}</td>
          <td class="py-3 text-gray-300">{{ $customer->email }}</td>
          <td class="py-3 text-gray-400">{{ $customer->phone ?? '—' }}</td>
          <td class="py-3">
            @if($customer->is_suspended)
              <span class="badge badge--danger">Suspended</span>
            @else
              <span class="badge badge--positive">Active</span>
            @endif
          </td>
          <td class="py-3 text-right">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-emerald-400 hover:text-emerald-300">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="py-6 text-center text-gray-500">No customers found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $customers->links() }}</div>
</div>
@endsection
