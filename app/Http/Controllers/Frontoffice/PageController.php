<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\ContactMessageMail;
use App\Models\Certificate;
use App\Models\Site;
use App\Models\Group;

class PageController extends Controller
{
    public function faq()
    {
        return view('frontoffice.faq');
    }

    public function contact()
    {
        return view('frontoffice.contact');
    }

    public function sites()
    {
        return view('frontoffice.sites');
    }

    public function intensiveCourses()
    {
        return view('frontoffice.intensive-courses');
    }

    public function onlineCourses()
    {
        $site = Site::where('slug', 'gls-online')->first();

        if (!$site) {
            return view('frontoffice.online-courses', [
                'groups' => collect(),
                'site' => null,
            ]);
        }

        $groups = Group::where('site_id', $site->id)->whereDate('date_fin', '>=', now())->orderBy('status')->orderBy('level')->get()->groupBy('period_label');

        return view('frontoffice.online-courses', [
            'groups' => $groups,
            'site' => $site,
        ]);
    }

    public function pricing()
    {
        return view('frontoffice.pricing');
    }

    public function glsExams()
    {
        return view('frontoffice.exams.gls');
    }

    public function osdExams()
    {
        return view('frontoffice.exams.osd');
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
            return redirect()->route('front.certificate.check')->with('certificate_error', 'Aucun certificat trouvé pour ce numéro.');
        }

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
                'public_token' => $certificate->public_token,
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

    public function niveauC1()
    {
        return view('frontoffice.niveaux.c1');
    }

    public function contactPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:5',
        ]);

        Mail::to('info@gls-sprachzentrum.ma')->queue(new ContactMessageMail($request->all()));

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
    public function goetheExams()
    {
        return view('frontoffice.exams.goethe');
    }
    public function terms()
    {
        return view('frontoffice.legal.terms');
    }

    public function privacy()
    {
        return view('frontoffice.legal.privacy');
    }
    public function fcMarokko()
    {
        return view('frontoffice.partners.fc-marokko');
    }

    public function discoverYourLevel()
    {
        return view('frontoffice.discover-your-level');
    }

    public function onlineRegistration()
    {
        return view('frontoffice.online-registration');
    }

    public function storeOnlineRegistration(Request $request)
    {
        // Validate the registration form
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'level' => 'required|string|in:A1,A2,B1,B2,no-idea',
            'course_type' => 'required|string|in:intensive,online,exam-prep',
            'message' => 'nullable|string|max:1000',
            'accept_terms' => 'required|accepted',
        ]);

        // TODO: Store registration in database or send email
        // For now, we'll just redirect with a success message

        return redirect()
            ->route('front.home')
            ->with('success', 'Thank you for your registration! We will contact you shortly.');
    }

    public function glsInscription()
    {
        return view('frontoffice.gls-inscription');
    }

    public function glsSuccess()
    {
        return view('frontoffice.gls-success');
    }

    public function lpMeta()
    {
        return view('frontoffice.landing.meta');
    }

    public function lpGoogle()
    {
        return view('frontoffice.landing.google');
    }

    public function lpTiktok()
    {
        return view('frontoffice.landing.tiktok');
    }
}
