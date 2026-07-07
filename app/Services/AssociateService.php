<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\BankDetail;
use App\Models\DesignationRank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AssociateService
{
    public function indexData($request)
    {
        $query = Associate::with(['rank', 'bankDetail', 'sponsor', 'underPlace']);
        if ($request->filled('joining_date')) {
            $query->whereDate('created_at', $request->joining_date);
        }
        if ($request->filled('associate_name')) {
            $query->where('associate_name', 'like', '%'.trim($request->associate_name).'%');
        }
        if ($request->filled('rank_id')) {
            $query->where('rank_id', $request->rank_id);
        }

        return [
            'associates' => $query->orderBy('id', 'desc')->get(),
            'ranks' => DesignationRank::orderBy('rank_number', 'desc')->get(),
        ];
    }

    public function createData()
    {
        $associates = Associate::with('rank')->get();
        $defaultRank = DesignationRank::orderByDesc('rank_number')->first();
        
        return ['associates' => $associates, 'defaultRank' => $defaultRank];
    }

    public function store(array $data)
    {
        DB::transaction(function () use (&$data) {
            $lastAssociate = Associate::latest('id')->first();
            $nextNumber = $lastAssociate ? $lastAssociate->id + 1 : 1;
            $associateCode = 'PRS'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            $data['photo'] = uploadFile($data['photo'] ?? null, 'associates/photo');
            $data['id_proof_photo'] = uploadFile($data['id_proof_photo'] ?? null, 'associates/id-proof');
            $data['pancard_photo'] = uploadFile($data['pancard_photo'] ?? null, 'associates/pancard_photo');
            $bankPassbook = uploadFile($data['bank_passbook'] ?? null, 'associates/passbook');
            $plainPassword = Str::upper(Str::random(8));
            $associate = Associate::create([
                'associate_id' => $associateCode,
                'sponsor_id' => $data['sponsor_id'] ?? null,
                'direction' => $data['direction'] ?? null,
                'under_place_id' => $data['under_place_id'] ?? null,
                // 'rank_id' => $data['rank_id'] ?? null,
                'associate_name' => $data['associate_name'],
                'gender' => $data['gender'] ?? null,
                'title' => $data['title'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'dob' => $data['dob'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($plainPassword),
                'plain_password' => $plainPassword,
                'pancard_number' => $data['pancard_number'] ?? null,
                'aadhar_number' => $data['aadhar_number'] ?? null,
                'photo' => $data['photo'],
                'id_proof_photo' => $data['id_proof_photo'],
                'pancard_photo' => $data['pancard_photo'],
            ]);

            BankDetail::create([
                'associate_id' => $associate->id,
                'bank_name' => $data['bank_name'] ?? null,
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'nominee_name' => $data['nominee_name'] ?? null,
                'nominee_relation' => $data['nominee_relation'] ?? null,
                'nominee_age' => $data['nominee_age'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'bank_passbook' => $bankPassbook,
            ]);
        });
    }

    public function editData($id)
    {
        return [
            'associate' => Associate::with('bankDetail')->findOrFail($id),
            'associates' => Associate::get(),
            'ranks' => DesignationRank::get(),
        ];
    }

    public function update(array $data, $id)
    {
        DB::transaction(function () use ($data, $id) {
            $associate = Associate::with('bankDetail')->findOrFail($id);
            $photo = uploadFile(
                $data['photo'] ?? null,
                'associates/photo',
                $associate->photo
            );

            $idProof = uploadFile(
                $data['id_proof_photo'] ?? null,
                'associates/id-proof',
                $associate->id_proof_photo
            );

            $panCardPhoto = uploadFile(
                $data['pancard_photo'] ?? null,
                'associates/pancard',
                $associate->pancard_photo
            );

            $bankPassbook = uploadFile(
                $data['bank_passbook'] ?? null,
                'associates/passbook',
                $associate->bankDetail?->bank_passbook
            );

            $associate->update([
                'sponsor_id' => $data['sponsor_id'] ?? null,
                'under_place_id' => $data['under_place_id'] ?? null,
                'direction' => $data['direction'] ?? null,
                // 'rank_id' => $data['rank_id'] ?? null,
                'associate_name' => $data['associate_name'],
                'gender' => $data['gender'] ?? null,
                'title' => $data['title'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'dob' => $data['dob'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'email' => $data['email'] ?? null,
                'pancard_number' => $data['pancard_number'] ?? null,
                'aadhar_number' => $data['aadhar_number'] ?? null,
                'photo' => $photo,
                'id_proof_photo' => $idProof,
                'pancard_photo' => $panCardPhoto,
            ]);

            $associate->bankDetail()->updateOrCreate(['associate_id' => $associate->id],
                [
                    'bank_name' => $data['bank_name'] ?? null,
                    'account_holder_name' => $data['account_holder_name'] ?? null,
                    'account_number' => $data['account_number'] ?? null,
                    'ifsc_code' => $data['ifsc_code'] ?? null,
                    'nominee_name' => $data['nominee_name'] ?? null,
                    'nominee_relation' => $data['nominee_relation'] ?? null,
                    'nominee_age' => $data['nominee_age'] ?? null,
                    'joining_date' => $data['joining_date'] ?? null,
                    'bank_passbook' => $bankPassbook,
                ]
            );

        });
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $associate = Associate::with('bankDetail')->findOrFail($id);
            if ($associate->bankDetail) {
                deleteFile($associate->bankDetail->bank_passbook);
                $associate->bankDetail->delete();
            }
            deleteFile($associate->photo);
            deleteFile($associate->id_proof_photo);
            $associate->delete();
        });
    }

    public function getExportData($request)
    {
        $query = Associate::with(['rank', 'bankDetail']);
        if ($request->filled('joining_date')) {
            $query->whereDate('created_at', $request->joining_date);
        }
        if ($request->filled('associate_name')) {
            $query->where('associate_name', 'like', '%'.$request->associate_name.'%');
        }
        if ($request->filled('rank_id')) {
            $query->where('rank_id', $request->rank_id);
        }

        return $query->latest()->get();
    }
}