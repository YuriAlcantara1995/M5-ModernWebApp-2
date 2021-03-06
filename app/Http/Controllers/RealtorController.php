<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Realtor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RealtorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sortBy', 'users.name');
        $order = $request->query('order', 'asc');
        $realtors = Realtor::join('users', 'users.id', '=', 'realtors.user_id')
        ->select('realtors.id', 'phone', 'user_id', 'users.name as user_name', 'users.email as user_email')
        ->orderBy($sortBy, $order)->paginate(5);

        $existRealtorProfile = (Auth::check()) && (Realtor::all()->where('user_id', Auth::user()->id)->count() != 0);

        cache()->forget('welcome_realtors');

        return view('realtors.index', compact('realtors', 'sortBy', 'order', 'existRealtorProfile'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        return view('realtors.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        ]);

        $user_id = Auth::id();

        $realtor = Realtor::create($request->all());
        $realtor->user_id = $user_id;
        $realtor->save();

        return redirect()->route('realtors.index')
                        ->with('success', 'Realtor created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Realtor  $realtor
     * @return \Illuminate\Http\Response
     */
    public function show(Realtor $realtor)
    {
        return view('realtors.show', compact('realtor'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Realtor  $realtor
     * @return \Illuminate\Http\Response
     */
    public function edit(Realtor $realtor)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! Gate::allows('update-realtor', $realtor)) {
            abort(403);
        }

        return view('realtors.edit', compact('realtor'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Realtor  $realtor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Realtor $realtor)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! Gate::allows('update-realtor', $realtor)) {
            abort(403);
        }

        $request->validate([
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        ]);

        $realtor->update($request->all());

        return redirect()->route('realtors.index')
                        ->with('success', 'Realtor updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Realtor  $realtor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Realtor $realtor)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! Gate::allows('delete-realtor', $realtor)) {
            abort(403);
        }

        $realtor->delete();

        return redirect()->route('realtors.index')
                        ->with('success', 'Realtor deleted successfully');
    }
}
