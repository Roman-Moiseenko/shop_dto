<?php

namespace App\Modules\Auth\Tests\Feature\Api\Auth;

use Illuminate\Foundation\Testing\TestCase;
use App\Modules\Auth\Infrastructure\Models\User;
use App\Modules\Auth\Infrastructure\Models\Staff as StaffModel;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        // Создаём роли
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'staff', 'guard_name' => 'web']);

        // Создаём администратора
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    /** @test */
    public function admin_can_create_staff(): void
    {
        $payload = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'middle_name' => 'Иванович',
            'position' => 'Разработчик',
            'department' => 'IT',
            'work_phone' => '+79001234567',
            'work_email' => 'ivanov@example.com',
            'hire_date' => '2025-01-01',
            'birth_date' => '1990-01-01',
            'telegram_chat_id' => '12345',
            'notes' => 'Тестовый сотрудник',
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_names' => ['staff'],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/admin/staff', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.full_name', 'Иванов Иван Иванович')
            ->assertJsonPath('data.position', 'Разработчик');

        $this->assertDatabaseHas('staff', [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
        ]);
    }

    /** @test */
    public function admin_can_update_staff(): void
    {
        // Создаём сотрудника
        $staff = StaffModel::create([
            'last_name' => 'Старая',
            'first_name' => 'Фамилия',
            'position' => 'Старая должность',
        ]);
        $user = User::factory()->create(['email' => 'old@example.com']);
        $staff->user()->save($user);

        $payload = [
            'last_name' => 'Новая',
            'first_name' => 'Фамилия',
            'position' => 'Новая должность',
            'name' => 'Новое Имя',
            'email' => 'new@example.com',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/v1/admin/staff/{$staff->id}", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'last_name' => 'Новая',
            'position' => 'Новая должность',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function admin_can_delete_staff(): void
    {
        $staff = StaffModel::create([
            'last_name' => 'Удаляемый',
            'first_name' => 'Сотрудник',
            'position' => 'Тест',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->deleteJson("/api/v1/admin/staff/{$staff->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
    }

    /** @test */
    public function admin_can_list_staff(): void
    {
        StaffModel::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/admin/staff');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function unauthorized_user_cannot_access_staff_endpoints(): void
    {
        // Без токена
        $this->getJson('/api/v1/admin/staff')->assertStatus(401);
        $this->postJson('/api/v1/admin/staff', [])->assertStatus(401);
    }

    /** @test */
    public function non_admin_cannot_create_staff(): void
    {
        $client = User::factory()->create();
        $client->assignRole('client');
        $token = $client->createToken('test')->plainTextToken;

        $payload = [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'position' => 'Разработчик',
            'name' => 'Иван',
            'email' => 'client@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/admin/staff', $payload);

        $response->assertStatus(403); // Forbidden
    }
}
