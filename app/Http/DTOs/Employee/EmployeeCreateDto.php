<?php



class EmployeeCreateDto
{
    public string $name_full;
    public string $email;
    public string $position;
    public ?int $manager_id;
    public string $role;
    public int $leave_balance;

    public function __construct(array $data)
    {

        $this->name_full = $data['name_full'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->position = $data['position'] ?? '';
        $this->manager_id = $data['manager_id'] ?? null;
        $this->role = $data['role'] ?? 'employee';
        $this->leave_balance = $data['leave_balance'] ?? 0;
    }

}
