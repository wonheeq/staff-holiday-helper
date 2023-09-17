<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Account;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    /*
    Returns all Courses
     */
    public function getAllCourses(Request $request, String $accountNo)
    {
        // Check if user exists for given accountNo
        if (!Account::where('accountNo', $accountNo)->first()) {
            // User does not exist, return exception
            return response()->json(['error' => 'Account does not exist.'], 500);
        } else {
            $courses = Course::get();
            return response()->json($courses);
        }
    }
}
