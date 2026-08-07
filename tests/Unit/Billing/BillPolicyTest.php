<?php

namespace Tests\Unit\Billing;

use App\Models\Bill;
use App\Models\Role;
use App\Models\User;
use App\Policies\BillPolicy;
use PHPUnit\Framework\TestCase;

final class BillPolicyTest extends TestCase
{
    private function userWithRole(string $roleName): User
    {
        $role = new Role(['name' => $roleName]);
        $role->exists = true;

        $user = new User(['name' => 'Test User']);
        $user->setRelation('role', $role);

        return $user;
    }

    private function bill(): Bill
    {
        $bill = new Bill();
        $bill->exists = true;

        return $bill;
    }

    public function test_admin_can_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->view($this->userWithRole('admin'), $this->bill()));
    }

    public function test_manager_can_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->view($this->userWithRole('manager'), $this->bill()));
    }

    public function test_cashier_can_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->view($this->userWithRole('cashier'), $this->bill()));
    }

    public function test_kitchen_cannot_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->view($this->userWithRole('kitchen'), $this->bill()));
    }

    public function test_waiter_cannot_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->view($this->userWithRole('waiter'), $this->bill()));
    }

    public function test_customer_cannot_view_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->view($this->userWithRole('customer'), $this->bill()));
    }

    public function test_admin_can_list_bills(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->viewAny($this->userWithRole('admin')));
    }

    public function test_cashier_can_list_bills(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->viewAny($this->userWithRole('cashier')));
    }

    public function test_kitchen_cannot_list_bills(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->viewAny($this->userWithRole('kitchen')));
    }

    public function test_admin_can_create_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->create($this->userWithRole('admin')));
    }

    public function test_cashier_can_create_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->create($this->userWithRole('cashier')));
    }

    public function test_waiter_cannot_create_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->create($this->userWithRole('waiter')));
    }

    public function test_admin_can_void_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->void($this->userWithRole('admin'), $this->bill()));
    }

    public function test_manager_can_void_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->void($this->userWithRole('manager'), $this->bill()));
    }

    public function test_cashier_cannot_void_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->void($this->userWithRole('cashier'), $this->bill()));
    }

    public function test_admin_can_delete_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->delete($this->userWithRole('admin'), $this->bill()));
    }

    public function test_manager_can_delete_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertTrue($policy->delete($this->userWithRole('manager'), $this->bill()));
    }

    public function test_cashier_cannot_delete_bill(): void
    {
        $policy = new BillPolicy();
        $this->assertFalse($policy->delete($this->userWithRole('cashier'), $this->bill()));
    }
}
