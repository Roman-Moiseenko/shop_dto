<?php

namespace App\Modules\Content\Tests\Unit\Application\Actions\SettingSite\Footer;

use App\Modules\Content\Application\Actions\SettingSite\Footer\SaveFooterSettingsUseCase;
use App\Modules\Content\Application\DTOs\SettingSite\Footer\FooterSettingsSaveData;
use App\Modules\Content\Application\DTOs\SettingSite\MenuPositionSaveData;
use App\Modules\Shared\Application\Interfaces\SettingRepositoryInterface;
use App\Modules\Shared\Domain\Exceptions\AccessDeniedException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Trait\MockPermission;

class SaveFooterSettingsUseCaseTest extends TestCase
{
    use MockPermission;

    private SettingRepositoryInterface $settingRepo;
    private SaveFooterSettingsUseCase $useCase;

    public function getModuleName(): string { return 'content'; }
    public function getEntityName(): string { return 'data'; }

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingRepo = Mockery::mock(SettingRepositoryInterface::class);
        $this->useCase = new SaveFooterSettingsUseCase($this->settingRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function saves_footer_settings_successfully(): void
    {
        $dto = new FooterSettingsSaveData(
            copyright: '© 2026',
            description: 'Footer description',
            menuPositions: [
                new MenuPositionSaveData(position: 'footer_main', menuId: 1),
            ],
        );

        $expectedData = [
            'copyright'   => '© 2026',
            'description' => 'Footer description',
            'menuPositions' => [
                ['position' => 'footer_main', 'menuId' => 1],
            ],
        ];

        $this->settingRepo->shouldReceive('set')
            ->once()
            ->with('content', 'footer', $expectedData);

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        $this->assertTrue(true); // исключений нет
    }

    #[Test]
    public function saves_footer_settings_with_minimal_data(): void
    {
        $dto = new FooterSettingsSaveData(
            copyright: 'Minimal',
            menuPositions: [],
        );

        $expectedData = [
            'copyright'    => 'Minimal',
            'description'  => null,
            'menuPositions' => [],
        ];

        $this->settingRepo->shouldReceive('set')
            ->once()
            ->with('content', 'footer', $expectedData);

        $this->useCase->execute($dto, $this->mockUserPermission(edit: true));
        $this->assertTrue(true);
    }

    #[Test]
    public function throws_access_denied_when_permission_missing(): void
    {
        $dto = new FooterSettingsSaveData(copyright: 'Test', menuPositions: []);

        $this->settingRepo->shouldNotReceive('set');

        $this->expectException(AccessDeniedException::class);
        $this->useCase->execute($dto, $this->mockUserPermission()); // edit: false
    }
}
