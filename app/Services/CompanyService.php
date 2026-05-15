<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{
    public function getAll()
    {
        return Company::latest()->get();
    }

    public function create(array $data)
    {
        $data['logo'] = uploadFile($data['logo'] ?? null,'companies');
        return Company::create($data);
    }

    public function find($id)
    {
        return Company::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $company = Company::findOrFail($id);

        if (isset($data['logo'])) {
            deleteFile($company->logo);
            $data['logo'] = uploadFile($data['logo'], 'companies');
        }

        $company->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'website_link' => $data['website_link'],
            'contact_no' => $data['contact_no'],
            'address' => $data['address'],
            'logo' => $data['logo'] ?? $company->logo,
        ]);

        return $company;
    }

    public function delete($id)
    {
        $company = Company::findOrFail($id);
        deleteFile($company->logo);

        return $company->delete();
    }
}
