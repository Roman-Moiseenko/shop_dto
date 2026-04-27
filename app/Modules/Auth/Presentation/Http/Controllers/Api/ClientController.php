<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\CreateClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Auth\Infrastructure\Models\User;
use App\Modules\Auth\Presentation\Http\Requests\StoreClientRequest;
use App\Modules\Auth\Presentation\Http\Requests\UpdateClientRequest;
use App\Modules\Auth\Presentation\Http\Resources\ClientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly CreateClientUseCase       $createClientUseCase,
        private readonly UserRepositoryInterface   $userRepository
    ) {}

    public function index(): JsonResponse
    {
        $clients = Client::with('user')->paginate();
        return ClientResource::collection($clients)->response();
    }

    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) {
            return response()->json(['message' => 'Клиент не найден'], Response::HTTP_NOT_FOUND);
        }
        return new ClientResource($client)->response();
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $dto = new ClientUserData(
            lastName: $request->last_name,
            firstName: $request->first_name,
            middleName: $request->middle_name,
            phone: $request->phone,
            email: $request->email,
            birthDate: $request->birth_date,
            gender: $request->gender,
            country: $request->country,
            city: $request->city,
            street: $request->street,
            region: $request->region,
            postalCode: $request->postal_code,
            agreeToNewsletter: $request->agree_to_newsletter ?? false,
            preferredLanguage: $request->preferred_language ?? 'ru',
            externalId: $request->external_id,
            name: $request->name,
            userEmail: $request->user_email,
            password: $request->password,
            roleNames: $request->role_names ?? ['client']
        );

        $client = $this->createClientUseCase->execute($dto);
        return new ClientResource($client)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function update(UpdateClientRequest $request, int $id): JsonResponse
    {
        $dto = new ClientUserData(
            lastName: $request->last_name,
            firstName: $request->first_name,
            middleName: $request->middle_name,
            phone: $request->phone,
            email: $request->email,
            birthDate: $request->birth_date,
            gender: $request->gender,
            country: $request->country,
            city: $request->city,
            street: $request->street,
            region: $request->region,
            postalCode: $request->postal_code,
            agreeToNewsletter: $request->agree_to_newsletter ?? false,
            preferredLanguage: $request->preferred_language ?? 'ru',
            externalId: $request->external_id,
            name: $request->name ?? '',
            userEmail: $request->user_email ?? '',
            password: $request->password ?? ''
        );

        $this->updateClientUseCase->execute($id, $dto);
        return response()->json(['message' => 'Клиент обновлён']);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->clientRepository->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Клиент не найден'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Получить профиль текущего аутентифицированного клиента.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasRole('client')) {
            return response()->json(['message' => 'Доступ запрещён'], Response::HTTP_FORBIDDEN);
        }

        $client = $this->clientRepository->findByUserId($user->id);

        if (!$client) {
            return response()->json(['message' => 'Профиль клиента не найден'], Response::HTTP_NOT_FOUND);
        }

        return new ClientResource($client)->response();
    }

    /**
     * Обновить профиль текущего аутентифицированного клиента.
     * @throws \DateMalformedStringException
     */
    public function updateProfile(UpdateClientRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->hasRole('client')) {
            return response()->json(['message' => 'Доступ запрещён'], Response::HTTP_FORBIDDEN);
        }

        $client = $this->clientRepository->findByUserId($user->id);

        if (!$client) {
            return response()->json(['message' => 'Профиль клиента не найден'], Response::HTTP_NOT_FOUND);
        }

        $dto = new ClientUserData(
            lastName: $request->last_name,
            firstName: $request->first_name,
            middleName: $request->middle_name,
            phone: $request->phone,
            email: $request->email,
            birthDate: $request->birth_date,
            gender: $request->gender,
            country: $request->country,
            city: $request->city,
            street: $request->street,
            region: $request->region,
            postalCode: $request->postal_code,
            agreeToNewsletter: $request->agree_to_newsletter ?? false,
            preferredLanguage: $request->preferred_language ?? 'ru',
            externalId: $request->external_id,
            name: $request->name ?? $user->name,
            userEmail: $request->user_email ?? $user->email,
            password: $request->password ?? ''
        );

        $this->updateClientUseCase->execute($client->getId(), $dto);

        // Обновляем также данные User, если они были изменены
        if ($request->has('name') || $request->has('user_email') || $request->has('password')) {
            $domainUser = $this->userRepository->findById($user->id);
            if ($domainUser) {
                // Используем существующий Use Case для обновления пользователя или обновим здесь
                // Для простоты обновим модель Eloquent напрямую (можно вынести в UserUpdateUseCase)
                if ($request->has('name')) {
                    $user->name = $request->name;
                }
                if ($request->has('user_email')) {
                    $user->email = $request->user_email;
                }
                if ($request->filled('password')) {
                    $user->password = bcrypt($request->password);
                }
                $user->save();
            }
        }

        $updatedClient = $this->clientRepository->findById($client->getId());
        return (new ClientResource($updatedClient))->response();
    }

    public function user_create(string $id)
    {

    }

    public function user(string $id)
    {

    }
}
