<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\SettingSite\Header;

use App\Modules\Content\Application\Actions\SettingSite\Header\SaveHeaderSettingsUseCase;
use App\Modules\Content\Application\DTOs\SettingSite\Header\HeaderSettingsSaveData;
use App\Modules\Content\Application\DTOs\SettingSite\MenuPositionSaveData;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Infrastructure\Exceptions\AccessDeniedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tests\Trait\MockPermission;
class SaveHeaderSettingsUseCaseTest extends TestCase
{
    use MockPermission;

    private SettingRepositoryInterface $settingRepo;
    private SaveHeaderSettingsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo = Mockery::mock(SettingRepositoryInterface::class);
        $this->useCase = new SaveHeaderSettingsUseCase($this->settingRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function saves_header_settings_successfully(): void
    {
        $dto = new HeaderSettingsSaveData(
            siteName: 'My Site',
            slogan: 'Best shop',
            logoUuid: 'uuid-logo',
            menuPositions: [new MenuPositionSaveData(position: 'main', menuId: 1)],
            searchEnabled: true,
            searchPlaceholder: 'Search...',
            searchActionUrl: '/search',
        );

        // Ожидаемый массив формируем вручную, без вызова $dto->toArray()
        $expectedData = [
            'siteName' => 'My Site',
            'slogan' => 'Best shop',
            'logoUuid' => 'uuid-logo',
            'menuPositions' => [
                ['position' => 'main', 'menuId' => 1],
            ],
            'searchEnabled' => true,
            'searchPlaceholder' => 'Search...',
            'searchActionUrl' => '/search',
        ];

        $this->settingRepo->shouldReceive('set')
            ->once()
            ->with('content', 'header', $expectedData);

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        $this->assertTrue(true);
    }

    #[Test]
    public function saves_header_settings_with_minimal_data(): void
    {
        $dto = new HeaderSettingsSaveData(
            siteName: 'Minimal',
            menuPositions: [],
            searchEnabled: false,
        );

        // Формируем ожидаемый массив вручную, не вызывая $dto->toArray()
        $expectedData = [
            'siteName'         => 'Minimal',
            'slogan'           => null,
            'logoUuid'         => null,
            'menuPositions'    => [],
            'searchEnabled'    => false,
            'searchPlaceholder'=> null,
            'searchActionUrl'  => null,
        ];

        $this->settingRepo->shouldReceive('set')
            ->once()
            ->with('content', 'header', $expectedData);

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        $this->assertTrue(true); // исключений нет
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new HeaderSettingsSaveData(
            siteName: 'Restricted',
            menuPositions: [],
            searchEnabled: false,
        );

        $this->settingRepo->shouldNotReceive('set');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // edit: false
    }
}
