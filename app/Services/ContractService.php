<?php


namespace App\Services;

use App\Models\Activity;
use App\Models\Contract;
use Illuminate\Support\Facades\Auth;

class ContractService extends Services
{
    public function list()
    {
        return Contract::with(['customer', 'user', 'broker'])->orderbyDesc('created_at')->get();
    }

    public function create(array $data)
    {
        $contract  =Contract::create($data);
        $this->logActivity('Created Contract: ' . $contract->name);
        return $contract;
    }

    public function update(Contract $contract, array $data)
    {
        $contract->update($data);
        $this->logActivity("Updated Contract: " . $contract->name);
        return $contract;
    }
    public function getById($id)
    {
        return Contract::with(['customer', 'user', 'broker'])->findOrFail($id);
    }

    public function delete(Contract $contract)
    {
        $this->logActivity("Deleted Contract: " . $contract->name);
        $contract->delete();
        return true;
    }
    
}

