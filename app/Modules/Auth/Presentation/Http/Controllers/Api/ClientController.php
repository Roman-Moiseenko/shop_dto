<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Client\CreateClientUseCase;
use App\Modules\Auth\Application\Actions\Client\CreateClientWithConsentUseCase;
use App\Modules\Auth\Application\Actions\Client\RemoveClientUseCase;
use App\Modules\Auth\Application\Actions\Client\UpdateClientUseCase;
use App\Modules\Auth\Application\Actions\User\ChangeUserCredentialsUseCase;
use App\Modules\Auth\Application\Actions\User\ConfirmEmailUseCase;
use App\Modules\Auth\Application\Actions\User\RegisterUserClientUseCase;
use App\Modules\Auth\Application\DTOs\Client\ClientCreateData;
use App\Modules\Auth\Application\DTOs\Client\ClientCreateWithConsentData;
use App\Modules\Auth\Application\DTOs\Client\ClientUpdateData;
use App\Modules\Auth\Application\DTOs\Client\ClientUserData;
use App\Modules\Auth\Application\DTOs\User\ChangeUserCredentialsData;
use App\Modules\Auth\Application\DTOs\User\RegisterUserData;
use App\Modules\Auth\Application\Interfaces\ClientRepositoryInterface;
use App\Modules\Auth\Application\Interfaces\UserRepositoryInterface;
use App\Modules\Auth\Infrastructure\Models\Client;
use App\Modules\Auth\Infrastructure\Models\User;
use App\Modules\Auth\Presentation\Http\Resources\ClientResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
        private readonly CreateClientUseCase       $createClientUseCase,
        private readonly CreateClientWithConsentUseCase  $createClientWithConsentUseCase,
        private readonly RegisterUserClientUseCase  $registerUserClientUseCase,
        private readonly ChangeUserCredentialsUseCase  $changeUserCredentialsUseCase,
        private readonly ConfirmEmailUseCase  $confirmEmailUseCase,
        private readonly UpdateClientUseCase $updateClientUseCase,
        private readonly RemoveClientUseCase $removeClientUseCase,
    ) {}

    public function index(): JsonResponse
    {
        $clients = Client::with('user')->paginate();
        return ClientResource::collection($clients)->response();
    }

    public function show(int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);
        if (!$client)
            return response()->json(['message' => 'Клиент не найден'], Response::HTTP_NOT_FOUND);

        return response()->json(ClientUserData::fromEntity($client), Response::HTTP_OK);
    }

    /**
     * Создание клиента менеджером
     */
    public function store(Request $request): JsonResponse
    {
        try {
        $dto = ClientCreateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $client = $this->createClientUseCase->execute($dto);
        return response()->json(ClientUserData::fromEntity($client), Response::HTTP_CREATED);
    }

    /**
     * Регистрация клиента самостоятельно
     * @throws \Throwable
     */
    public function registration(Request $request): JsonResponse
    {
        try {
            $dtoClient = ClientCreateWithConsentData::validateAndCreate($request->all());
            $dtoUser = RegisterUserData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return DB::transaction(function () use ($dtoClient, $dtoUser) {
            $client = $this->createClientWithConsentUseCase->execute(
                $dtoClient
            );
            $user = $this->registerUserClientUseCase->execute(
                $client->id,
                $dtoUser
            );
            $client->user = $user;
            return response()->json(ClientUserData::fromEntity($client), Response::HTTP_OK);
        });
    }

    /**
     * Смена регистрационных данных клиентом
     */
    public function credentials(Request $request): JsonResponse
    {
        $user = $request->user();
        // 1. Проверяем, что пользователь привязан к профилю клиента
        if ($user->profileable_type !== Client::class) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        try {
            $dto = ChangeUserCredentialsData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->changeUserCredentialsUseCase->execute($user->id, $dto);
        return response()->json(null, Response::HTTP_OK);
    }

    /**
     * Подтверждение почты
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);
        try {
            $this->confirmEmailUseCase->execute($request->token);
            return response()->json(['message' => 'Email успешно подтверждён']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Регистрация клиента менеджером, если клиент уже был создан ранее
     */
    public function register(Request $request, int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);
        $password = $request->validate(['password' => 'required|string'])['password'];
        if (!$client) {
            return response()->json(['message' => 'Клиент не найден'], Response::HTTP_NOT_FOUND);
        }
        $dto = new RegisterUserData($client->email->value, (string)$password);
        $user = $this->registerUserClientUseCase->execute($id, $dto);
        $client->user = $user;
        return response()->json(ClientUserData::fromEntity($client), Response::HTTP_OK);
    }

    /**
     * Изменение клиента менеджером
     * @throws \DateMalformedStringException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);
        if (!$client)
            return response()->json(['message' => 'Клиент не найден'], 404);

        try {
            $dto = ClientUpdateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $client = $this->updateClientUseCase->execute($id, $dto);
        return response()->json(ClientUserData::fromEntity($client), Response::HTTP_OK);
        //return response()->json(['message' => 'Клиент обновлён']);
    }

    public function destroy(int $id): JsonResponse
    {
        $client = $this->clientRepository->findById($id);
        if (!$client) return response()->json(['message' => 'Клиент не найден'], Response::HTTP_NOT_FOUND);

        $deleted = $this->removeClientUseCase->execute($id);

        if (!$deleted)
            return response()->json(['message' => 'Ошибка удаления клиента'], Response::HTTP_NOT_MODIFIED);

        return response()->json(null, Response::HTTP_OK);
    }

    /**
     * Получить профиль текущего аутентифицированного клиента.
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        \Log::warning($user->id);
        if (!$user->hasRole('client')) {
            return response()->json(['message' => 'Доступ запрещён'], Response::HTTP_FORBIDDEN);
        }

        $client = $this->clientRepository->findByUserId($user->id);

        if (!$client) {
            return response()->json(['message' => 'Профиль клиента не найден'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(ClientUserData::fromEntity($client), Response::HTTP_CREATED);
    }

    /**
     * Обновить профиль текущего аутентифицированного клиента.
     * @throws \DateMalformedStringException
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // 1. Проверяем, что пользователь привязан к профилю клиента
        if ($user->profileable_type !== Client::class) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        // 2. Получаем ID клиента из собственного профиля пользователя
        $clientId = $user->profileable_id;
        try {
            $dto = ClientUpdateData::validateAndCreate($request->all());
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

            // 3. Вызываем тот же Use Case, но с ID, полученным из аутентификации
        $this->updateClientUseCase->execute($clientId, $dto);

        return response()->json(['message' => 'Профиль успешно обновлён']);
    }

}
