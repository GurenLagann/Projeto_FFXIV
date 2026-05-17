<?php

namespace App\Http\Controllers;

use App\Enums\CostMetric;
use App\Enums\RevenueMetric;
use App\Models\Alert;
use App\Models\Item;
use App\Models\Server;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = auth()->user()->alerts()->with(['item', 'server'])->latest()->paginate(10);
        return view('alerts.index', compact('alerts'));
    }

    public function create()
    {
        $items    = Item::where('is_craftable', true)->orderBy('name')->get();
        $servers  = Server::active()->orderBy('name')->get();
        $costMetrics = CostMetric::cases();
        $revMetrics  = RevenueMetric::cases();
        return view('alerts.create', compact('items', 'servers', 'costMetrics', 'revMetrics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'   => 'required|exists:items,id',
            'server_id' => 'required|exists:servers,id',
            'min_profit'=> 'required|integer|min:0',
            'min_margin'=> 'required|numeric|min:0|max:100',
        ]);

        auth()->user()->alerts()->create($validated);

        return redirect()->route('alerts.index')->with('success', 'Alert created successfully.');
    }

    public function toggle(Alert $alert)
    {
        abort_unless($alert->user_id === auth()->id(), 403);
        $alert->update(['is_active' => !$alert->is_active]);
        return back()->with('success', 'Alert updated.');
    }

    public function destroy(Alert $alert)
    {
        abort_unless($alert->user_id === auth()->id(), 403);
        $alert->delete();
        return redirect()->route('alerts.index')->with('success', 'Alert deleted.');
    }
}
