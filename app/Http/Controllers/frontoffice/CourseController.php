<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Group;

class CourseController extends Controller
{
    public function courses()
    {
        $levels = [
            'A1' => [
                'color' => 'a1-color',
                'letter' => 'A', 
                'number' => '1',
                'groups' => Group::where('level', 'A1')->get(),
            ],
            'A2' => [
                'color' => 'a2-color',
                'letter' => 'A',
                'number' => '2',
                'groups' => Group::where('level', 'A2')->get(),
            ],
            'B1' => [
                'color' => 'b1-color',
                'letter' => 'B',
                'number' => '1',
                'groups' => Group::where('level', 'B1')->get(),
            ],
            'B2' => [
                'color' => 'b2-color',
                'letter' => 'B',
                'number' => '2',
                'groups' => Group::where('level', 'B2')->get(),
            ],
        ];

        return view('frontoffice.courses.index', compact('levels'));
    }
}
