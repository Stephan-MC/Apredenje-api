<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternRequest;
use App\Http\Requests\UpdateInternRequest;
use App\Models\Intern;

class InternController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Intern $intern)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInternRequest $request, Intern $intern)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Intern $intern)
    {
        //
    }
}
