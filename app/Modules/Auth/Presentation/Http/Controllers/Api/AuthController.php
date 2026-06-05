<?php

namespace App\Modules\Auth\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Application\Actions\Auth\LoginUserUseCase;
use App\Modules\Auth\Application\Actions\Auth\LogoutUserUseCase;
use App\Modules\Auth\Application\Actions\Auth\ResetPasswordUseCase;
use App\Modules\Auth\Application\Actions\Auth\SendPasswordResetLinkUseCase;
use App\Modules\Auth\Application\Actions\User\GetUserProfileUseCase;
use App\Modules\Auth\Application\DTOs\LoginData;
use App\Modules\Auth\Domain\Exceptions\InvalidCredentialsException;
use App\Modules\Auth\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        // private readonly RegisterUserUseCase          $registerUser,
        private readonly LoginUserUseCase             $loginUser,
        private readonly LogoutUserUseCase            $logoutUser,
        private readonly SendPasswordResetLinkUseCase $sendResetLink,
        private readonly ResetPasswordUseCase         $resetPassword,
        private readonly GetUserProfileUseCase        $getUserProfileUseCase
        //  private readonly AssignRoleToUserUseCase      $assignRoleUser,
    )
    {
    }

    /*
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $dto = new RegisterUserData(...$request->validated());
            $user = $this->registerUser->execute($dto);
            $this->assignRoleUser->execute($user->id, RoleName::CLIENT);

            return new UserResource($user)
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (UserAlreadyExistsException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }
*/
    public function login(Request $request): JsonResponse
    {
        try {
            $dto = LoginData::validateAndCreate($request);
        } catch (InvalidCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
        $token = $this->loginUser->execute($dto);
        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->logoutUser->execute($request);
        return response()->json(['message' => 'Выход выполнен']);
    }

    /*
        public function user(Request $request): JsonResponse
        {
            // Получаем аутентифицированного пользователя через guard
            $user = $request->user();
            // Здесь нужно преобразовать модель Eloquent в доменную сущность, если необходимо
            // Для простоты можно вернуть модель, но лучше через репозиторий
            return response()->json($user);
        }
    */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->sendResetLink->execute($request->email);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], Response::HTTP_BAD_REQUEST);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->resetPassword->execute($request->only(
            'email', 'password', 'password_confirmation', 'token'
        ));

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], Response::HTTP_BAD_REQUEST);
    }

    public function profile(): JsonResponse
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        $data = $this->getUserProfileUseCase->execute($authUser->id);
        return response()->json($data);
    }
}
