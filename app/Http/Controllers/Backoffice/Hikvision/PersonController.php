<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionPerson;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionPerson::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $persons = $query->latest('updated_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => HikvisionPerson::count(),
            'active' => HikvisionPerson::whereIn('status', ['active', 'enabled'])->count(),
            'inactive' => HikvisionPerson::whereIn('status', ['inactive', 'disabled'])->count(),
            'without_email' => HikvisionPerson::whereNull('email')->count(),
        ];

        return view('backoffice.hikvision.persons.index', compact('persons', 'summary'));
    }

    public function show(HikvisionPerson $person)
    {
        $recentAttendance = $person->attendanceRecords()
            ->with('device')
            ->latest('occurred_at')
            ->limit(20)
            ->get();

        return view('backoffice.hikvision.persons.show', compact('person', 'recentAttendance'));
    }
}
