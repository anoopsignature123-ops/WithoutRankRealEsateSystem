<?php

namespace App\Services;

use App\Models\Block;
use App\Models\PlotDetail;

class PlotDetailService
{
    public function getAll($request = null)
    {
        $query = PlotDetail::with(['project', 'block', 'plotType']);
        if ($request) {
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('plot_number')) {
                $query->where('plot_number', 'like', '%'.$request->plot_number.'%');
            }
        }

        return $query->get();
    }

    public function store(array $data)
    {
        $block = Block::findOrFail(
            $data['block_id']
        );
        $data['plot_number'] =
        $block->block.'-'.$data['plot_number'];

        return PlotDetail::create($data);
    }

    public function show($id)
    {
        return PlotDetail::findOrFail($id);
    }

    public function find($id)
    {
        return PlotDetail::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $plot = $this->find($id);
        $block = Block::findOrFail($data['block_id']);
        $plotNumber = preg_replace('/^[A-Z]+-/','',$data['plot_number']);
        $data['plot_number'] =$block->block.'-'.$plotNumber;
        $plot->update($data);
        return $plot;
    }

    public function delete($id)
    {
        return $this->find($id)->delete();
    }
}