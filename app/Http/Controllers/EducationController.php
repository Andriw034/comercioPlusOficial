<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function phonicsIndex()
    {
        return view('yeargroups.EYFS.y0.subjects.phonics.index');
    }

    public function understandingTheWorldIndex()
    {
        return view('yeargroups.EYFS.y0.subjects.understanding-the-world.index');
    }

    public function mathsAutumnIndex()
    {
        return view('yeargroups.EYFS.y0.subjects.maths.autumn.index');
    }

    public function mathsAutumnWeek1()
    {
        return view('yeargroups.EYFS.y0.subjects.maths.autumn.week1');
    }

    public function mathsAutumnWeek2()
    {
        return view('yeargroups.EYFS.y0.subjects.maths.autumn.week2');
    }

    public function lessonCountingTo3()
    {
        return view('lesson.counting-to-3');
    }
}
