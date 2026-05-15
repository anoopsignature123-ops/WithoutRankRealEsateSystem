<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\AssociateAdvance;

class AssociateAdvanceService
{
    public function getAssociates()
    {
        return Associate::orderBy('id')->get();
    }

    public function getAll()
    {
        return AssociateAdvance::with('associate')->latest()->get();
    }

    public function findById($id)
    {
        return AssociateAdvance::findOrFail($id);
    }

    public function store(array $data)
    {
        return AssociateAdvance::create($data);
    }

    public function update($id, array $data)
    {
        $advance = $this->findById($id);
        $advance->update($data);
        return $advance;
    }

    public function delete($id)
    {
        $advance = $this->findById($id);
        $advance->delete();
        return true;
    }
}