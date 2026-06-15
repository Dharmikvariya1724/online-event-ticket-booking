@extends('layouts.admin')

@section('title', 'Organisers')
@section('page-title', 'Organisers')
@section('page-subtitle', 'Review organiser accounts and approvals')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
  <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, company, email"
      class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Approvals</option>
      <option value="approved" @selected(request('status')==='approved')>Approved</option>
      <option value="pending" @selected(request('status')==='pending')>Pending</option>
      <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
    </select>
    <select name="account_status" class="bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Status</option>
      <option value="active" @selected(request('account_status')==='active')>Active</option>
      <option value="suspended" @selected(request('account_status')==='suspended')>Suspended</option>
    </select>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Search</button>
    <a href="{{ route('admin.organisers.index') }}" class="px-4 py-2 rounded-xl bg-gray-700 text-white text-sm font-semibold text-center">Clear</a>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-gray-400 text-xs uppercase">
        <tr>
          <th class="text-left py-3">Organiser</th>
          <th class="text-left py-3">Company</th>
          <th class="text-left py-3">Email</th>
          <th class="text-left py-3">Approval</th>
          <th class="text-left py-3">Status</th>
          <th class="text-right py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($organisers as $organiser)
        <tr>
          <td class="py-3 text-white font-semibold">{{ $organiser->name }}</td>
          <td class="py-3 text-gray-300">{{ $organiser->company_name }}</td>
          <td class="py-3 text-gray-400">{{ $organiser->email }}</td>
          <td class="py-3">
            @if($organiser->is_approved)
              <span class="badge badge--positive">Approved</span>
            @elseif($organiser->rejected_at)
              <span class="badge badge--danger">Rejected</span>
            @else
              <span class="badge badge--warning">Pending</span>
            @endif
          </td>
          <td class="py-3">
            @if($organiser->is_suspended)
              <span class="badge badge--danger">Suspended</span>
            @else
              <span class="badge badge--positive">Active</span>
            @endif
          </td>
          <td class="py-3 text-right">
            <a href="{{ route('admin.organisers.show', $organiser->id) }}" class="text-emerald-400 hover:text-emerald-300">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="py-6 text-center text-gray-500">No organisers found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $organisers->links() }}</div>
</div>
@endsection
