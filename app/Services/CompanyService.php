<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{
    public function update(int $id, array $data)
    {
        $company = Company::findOrFail($id);
        $company->update($data);

        return $company;
    }
    public function getById($id)
    {
        return Company::findOrFail($id);
    }
}
