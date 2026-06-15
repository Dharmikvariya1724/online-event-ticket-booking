<?php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with(['ticketTiers', 'organiser'])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit(12)
            ->get();

        $categories = Event::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'approved');
            })
            ->where('starts_at', '>', now())
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'category');

        $stats = [
            'total_events'    => Event::where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('approval_status')
                      ->orWhere('approval_status', 'approved');
                })->count(),
            'upcoming_events' => Event::where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('approval_status')
                      ->orWhere('approval_status', 'approved');
                })->where('starts_at', '>', now())->count(),
        ];

        return view('home', compact('events', 'categories', 'stats'));
    }
}
