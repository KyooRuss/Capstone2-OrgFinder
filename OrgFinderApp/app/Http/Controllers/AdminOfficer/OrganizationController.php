<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\Organization;

class OrganizationController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
    }

    public function index()
    {
        $organization = $this->myOrganization();

        return view('admin-officer.organization.index', compact('organization'));
    }
}
