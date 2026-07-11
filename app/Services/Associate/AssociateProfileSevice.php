<?php

namespace App\Services\Associate;

use App\Models\Associate;
use Illuminate\Support\Facades\Hash;

class AssociateProfileSevice
{
    public function updateProfile(Associate $associate, array $data): Associate
    {
        $photo = uploadFile($data['photo'] ?? null, 'associates/photo', $associate->photo);
        $idProof = uploadFile($data['id_proof_photo'] ?? null, 'associates/id-proof', $associate->id_proof_photo);
        $panCardPhoto = uploadFile($data['pancard_photo'] ?? null, 'associates/pancard', $associate->pancard_photo);
        $bankPassbook = uploadFile($data['bank_passbook'] ?? null, 'associates/passbook', $associate->bankDetail?->bank_passbook);
        $associate->update([
            'associate_name' => $data['associate_name'],
            'gender' => $data['gender'],
            'father_name' => $data['father_name'],
            'dob' => $data['dob'],
            'mobile_number' => $data['mobile_number'],
            'email' => strtolower($data['email']),
            'pancard_number' => strtoupper($data['pancard_number']),
            'aadhar_number' => $data['aadhar_number'],
            'address' => $data['address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'photo' => $photo,
            'id_proof_photo' => $idProof,
            'pancard_photo' => $panCardPhoto,
        ]);

        $associate->bankDetail()->updateOrCreate(
            ['associate_id' => $associate->id],
            [
                'bank_name' => strtoupper($data['bank_name']),
                'account_number' => $data['account_number'],
                'ifsc_code' => strtoupper($data['ifsc_code']),
                'account_holder_name' => $data['account_holder_name'],
                'nominee_name' => $data['nominee_name'],
                'nominee_relation' => $data['nominee_relation'],
                'nominee_age' => $data['nominee_age'],
                'bank_passbook' => $bankPassbook,
            ]
        );

        return $associate->fresh('bankDetail');
    }

    public function changePassword($associate, array $data)
    {
        return $associate->update([
            'password' => Hash::make($data['new_password']),
            'plain_password' => $data['new_password'],
        ]);
    }

    public function findAssociateForLetter($id)
    {
        return Associate::with(['sponsor'])->findOrFail($id);
    }
}
