@extends('layouts.organiser')
@section('title', 'Promo Code Details')
@section('page-title', 'Promo Code Details')
@section('page-subtitle', $promo->code)

@section('content')
<div class="max-w-3xl">
  <div class="flex items-center justify-between mb-5">
    <a href="{{ route('organiser.promos.index') }}" class="text-sm text-gray-400 hover:text-white">&larr; All Promo Codes</a>
    
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Code</div>
        <div class="text-sm font-mono font-bold text-indigo-400">{{ $promo->code }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Discount</div>
        <div class="text-sm font-semibold text-white">
          {{ $promo->type === 'percentage' ? $promo->value . '%' : ticketly_money_code($promo->value) }}
          @if($promo->max_discount)
          <span class="text-xs text-gray-500">(max {{ ticketly_money($promo->max_discount) }})</span>
          @endif
        </div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Event</div>
        <div class="text-sm font-semibold text-white">{{ $promo->event?->title ?? 'All events' }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Status</div>
        <div class="text-sm">
          @if($promo->trashed())
          <span class="badge badge--neutral">Deleted</span>
          @elseif(!$promo->isValid())
          <span class="badge badge--danger">Expired</span>
          @elseif($promo->is_active)
          <span class="badge badge--positive">Active</span>
          @else
          <span class="badge badge--neutral">Inactive</span>
          @endif
        </div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Uses</div>
        <div class="text-sm font-semibold text-white">{{ $promo->used_count }}{{ $promo->max_uses ? '/' . $promo->max_uses : '' }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Expires</div>
        <div class="text-sm font-semibold text-white">{{ $promo->expires_at ? ticketly_format_date($promo->expires_at) : '-' }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Created</div>
        <div class="text-sm font-semibold text-white">{{ ticketly_format_datetime($promo->created_at) }}</div>
      </div>
      <div>
        <div class="text-xs text-gray-500 mb-0.5">Deleted At</div>
        <div class="text-sm font-semibold text-white">{{ ticketly_format_datetime($promo->deleted_at, '-') }}</div>
      </div>
    </div>
  </div>
</div>
@endsection
