<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Associate\AssociateProfileRequest;
use App\Http\Requests\Associate\ChangePasswordRequest;
use App\Models\Company;
use App\Services\Associate\AssociateProfileSevice;
use App\Services\LocationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssociateProfileController extends Controller
{
    protected $profileService;

    public function __construct(
        AssociateProfileSevice $profileService,
        protected LocationService $locationService
    )
    {
        $this->profileService = $profileService;
    }

    public function viewProfile(Request $request)
    {
        $associate = auth()->guard('associate')->user()->load(['sponsor', 'rank', 'bankDetail']);

        return view('associate-panel.profile.view_profile', compact('associate'));
    }

    public function editProfile(Request $request)
    {
        $associate = auth()->guard('associate')->user()->load(['sponsor', 'rank', 'bankDetail']);
        $states = $this->locationService->getStates();

        return view('associate-panel.profile.edit_profile', compact('associate', 'states'));
    }

    public function updateProfile(AssociateProfileRequest $request)
    {
        $associate = Auth::guard('associate')->user();
        $this->profileService->updateProfile($associate, $request->validated());

        return redirect()
            ->route('associate-panel.view-profile')
            ->with('success', 'Profile updated successfully!');
    }

    public function changePassword()
    {
        return view('associate-panel.profile.change_password');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        $associate = Auth::guard('associate')->user();
        $this->profileService->changePassword($associate, $request->validated());
        Auth::guard('associate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('associate-panel.login')
            ->with('success', 'Password changed successfully! Please login with your new credentials.');
    }

    public function downloadPdf()
    {
        $associateId = Auth::guard('associate')->id();
        $company = Company::where('status', '1')->first();
        $associate = $this->profileService->findAssociateForLetter($associateId);
        $pdf = Pdf::loadView('associate-panel.profile.welcome_letter_pdf', compact('associate', 'company'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('welcome-letter-'.($associate->associate_id ?? 'associate').'.pdf');
    }
}
