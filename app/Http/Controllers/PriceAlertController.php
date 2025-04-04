<?php
namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceAlertController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $alerts = PriceAlert::where('user_id', Auth::id())
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'target_price' => 'required|numeric|min:0'
        ]);
        
        PriceAlert::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'target_price' => $request->target_price,
            'is_active' => true
        ]);
        
        return back()->with('success', 'Alerta de precio creada correctamente');
    }

    public function destroy(PriceAlert $alert)
    {
        if ($alert->user_id != Auth::id()) {
            abort(403);
        }
        
        $alert->delete();
        
        return back()->with('success', 'Alerta eliminada correctamente');
    }
}