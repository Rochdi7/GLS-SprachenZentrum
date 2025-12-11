<?php

namespace App\Http\Controllers\Frontoffice;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessageMail;
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

        if (!$certificate) {
            return redirect()->route('front.certificate.check')->with('certificate_error', 'Aucun certificat trouvé pour ce numéro. Vérifiez le numéro et réessayez.');
        }

        // Store correct fields
        return redirect()
            ->route('front.certificate.check')
            ->with('certificate_success', [
                'id' => $certificate->id,
                'first_name' => $certificate->first_name,
                'last_name' => $certificate->last_name,

                'exam_level' => $certificate->exam_level,

                'exam_date' => $certificate->exam_date,
                'issued_date' => $certificate->issue_date, 
                'certificate_number' => $certificate->certificate_number,
            ]);
    }

    public function niveauA1()
    {
        return view('frontoffice.niveaux.a1');
    }

    public function niveauA2()
    {
        return view('frontoffice.niveaux.a2');
    }

    public function niveauB1()
    {
        return view('frontoffice.niveaux.b1');
    }

    public function niveauB2()
    {
        return view('frontoffice.niveaux.b2');
    }
    public function courses()
    {
        return view('frontoffice.courses.index');
    }

    public function contactPost(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|min:5',
            ]);

            // ENVOI EMAIL RÉEL
            Mail::to('rochdi.karouali1234@gmail.com')->send(new ContactMessageMail($request->all()));

            return back()->with('success', 'Votre message a bien été envoyé ! Merci de nous avoir contactés.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}
