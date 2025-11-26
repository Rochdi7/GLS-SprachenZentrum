<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;

class PageController extends Controller
{
    // ============================
    // FAQ + CONTACT
    // ============================

    public function faq()
    {
        return view('frontoffice.faq');
    }

    public function contact()
    {
        return view('frontoffice.contact');
    }

    // ============================
    // SITES (Centers)
    // ============================

    public function sites()
    {
        return view('frontoffice.sites');
    }

    public function siteRabat()
    {
        return view('frontoffice.sites.rabat');
    }

    public function siteSale()
    {
        return view('frontoffice.sites.sale');
    }

    public function siteKenitra()
    {
        return view('frontoffice.sites.kenitra');
    }

    public function siteCasablanca()
    {
        return view('frontoffice.sites.casablanca');
    }

    public function siteAgadir()
    {
        return view('frontoffice.sites.agadir');
    }

    public function siteMarrakech()
    {
        return view('frontoffice.sites.marrakech');
    }

    // ============================
    // COURSES
    // ============================

    public function intensiveCourses()
    {
        return view('frontoffice.intensive-courses');
    }

    public function onlineCourses()
    {
        return view('frontoffice.online-courses');
    }

    public function pricing()
    {
        return view('frontoffice.pricing');
    }

    // ============================
    // EXAMS
    // ============================

    public function glsExams()
    {
        return view('frontoffice.exams.gls');
    }

    public function osdExams()
    {
        return view('frontoffice.exams.osd');
    }

    // ============================
    // RESOURCES
    // ============================

    public function blog()
    {
        return view('frontoffice.blog.blog');
    }
    public function blogdetails()
    {
        return view('frontoffice.blog.blog-details');
    }
    public function studentStories()
    {
        return view('frontoffice.resources.student-stories');
    }

    public function certificateCheck()
{
    return view('frontoffice.certificates.check');
}

public function certificateCheckPost(Request $request)
{
    $request->validate([
        'certificate_number' => 'required',
    ]);

    $certificate = Certificate::where('certificate_number', $request->certificate_number)->first();

    if (! $certificate) {
        return redirect()
            ->route('front.certificate.check')
            ->with('certificate_error', 'Aucun certificat trouvé pour ce numéro. Vérifiez le numéro et réessayez.');
    }

    // On ne stocke pas tout l'objet, juste ce qu’on a besoin
    return redirect()
        ->route('front.certificate.check')
        ->with('certificate_success', [
            'id' => $certificate->id,
            'first_name' => $certificate->first_name,
            'last_name' => $certificate->last_name,
            'level' => $certificate->level,
            'exam_date' => $certificate->exam_date,
            'issued_date' => $certificate->issued_date,
            'certificate_number' => $certificate->certificate_number,
        ]);
}

}
