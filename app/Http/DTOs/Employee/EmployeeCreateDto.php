<?php

namespace App\Http\DTOs\Employee;


class EmployeeCreateDto
{
    public string $full_name;
    public string $email;
    public ?string $position;
    public ?int $manager_id;
    public string $role;
    public float $leave_balance;

    public function __construct(array $data)
    {

        $this->full_name = $data['full_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->position = $data['position'] ?? null;
        $this->manager_id = $data['manager_id'] ?? null;
        $this->role = $data['role'] ?? 'employee';
        $this->leave_balance = isset($data['leave_balance']) ? (float)$data['leave_balance'] : 0.0;


    }

}
