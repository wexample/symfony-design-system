<?php

namespace Wexample\SymfonyDesignSystem\Tests\Fixtures\Entity;

use Wexample\SymfonyHelpers\Entity\Interfaces\AbstractEntityInterface;

class TestEntity implements AbstractEntityInterface
{
    public function __construct(private ?int $id = null) {}
    public function getId(): ?int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }
}

class NoDtoEntity implements AbstractEntityInterface
{
    public function __construct(private ?int $id = null) {}
    public function getId(): ?int { return $this->id; }
    public function setId(int $id) { $this->id = $id; }
}

namespace App\Api\Dto;

use Wexample\SymfonyHelpers\Api\Dto\EntityDto;

class TestEntity extends EntityDto {}

namespace Wexample\SymfonyDesignSystem\Tests\Unit\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Wexample\SymfonyDesignSystem\Rendering\Asset;
use Wexample\SymfonyDesignSystem\Rendering\AssetsRegistry;
use Wexample\SymfonyDesignSystem\Rendering\RenderNode\Traits\DesignSystemRenderNodeTrait;
use Wexample\SymfonyDesignSystem\Rendering\RenderPass;
use Wexample\SymfonyDesignSystem\Service\JsService;
use Wexample\SymfonyHelpers\Api\Dto\EntityDto;
use Wexample\SymfonyTesting\Tests\AbstractSymfonyKernelTestCase;
use Wexample\WebRenderNode\Rendering\RenderNode\AbstractRenderNode;

class JsServiceTest extends AbstractSymfonyKernelTestCase
{
    public function testVarExportSetsSerializedValueOnContextNode(): void
    {
        [$renderPass, $renderNode] = $this->createRenderContext();

        $service = new JsService(
            $this->createStub(NormalizerInterface::class),
            new ParameterBag()
        );

        $service->varExport($renderPass, 'foo', ['bar' => 123]);

        $this->assertSame(['foo' => ['bar' => 123]], $renderNode->getVars());
    }

    public function testVarEnvExportReadsFromParameterBag(): void
    {
        [$renderPass, $renderNode] = $this->createRenderContext();

        $service = new JsService(
            $this->createStub(NormalizerInterface::class),
            new ParameterBag(['my_param' => 'value-from-parameter'])
        );

        $service->varEnvExport($renderPass, 'my_param');

        $this->assertSame(['my_param' => 'value-from-parameter'], $renderNode->getVars());
    }

    public function testSerializeEntityNormalizesWhenDtoExists(): void
    {
        $entity = new \Wexample\SymfonyDesignSystem\Tests\Fixtures\Entity\TestEntity(42);

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with(
                $entity,
                'jsonld',
                $this->callback(function (array $context): bool {
                    return ($context['displayFormat'] ?? null) === EntityDto::DISPLAY_FORMAT_DEFAULT
                        && ($context['collection_operation_name'] ?? null) === 'twig_serialize_entity';
                })
            )
            ->willReturn(['id' => 42]);

        $service = new JsService($normalizer, new ParameterBag());

        $this->assertSame(['id' => 42], $service->serializeEntity($entity));
    }

    public function testSerializeEntityReturnsNullWhenNoDto(): void
    {
        $entity = new \Wexample\SymfonyDesignSystem\Tests\Fixtures\Entity\NoDtoEntity(7);

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->never())->method('normalize');

        $service = new JsService($normalizer, new ParameterBag());

        $this->assertNull($service->serializeEntity($entity));
    }

    public function testSerializeValueDelegatesToSerializeEntity(): void
    {
        $entity = new \Wexample\SymfonyDesignSystem\Tests\Fixtures\Entity\TestEntity(21);

        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with(
                $entity,
                'jsonld',
                $this->callback(fn (array $context): bool =>
                    ($context['displayFormat'] ?? null) === EntityDto::DISPLAY_FORMAT_DEFAULT
                    && ($context['collection_operation_name'] ?? null) === 'twig_serialize_entity'
                )
            )
            ->willReturn(['id' => 21]);

        $service = new JsService($normalizer, new ParameterBag());

        $this->assertSame(['id' => 21], $service->serializeValue($entity));
    }

    /**
     * @return array{RenderPass, AbstractRenderNode}
     */
    private function createRenderContext(): array
    {
        $renderPass = new RenderPass(
            'bundle/view',
            new AssetsRegistry($this->getFixtureProjectDir())
        );

        $renderNode = new class extends AbstractRenderNode {
            use DesignSystemRenderNodeTrait;
            public function getContextType(): string
            {
                return Asset::CONTEXT_PAGE;
            }
        };

        $renderNode->init($renderPass, 'bundle/view');
        $renderPass->setCurrentContextRenderNode($renderNode);

        return [$renderPass, $renderNode];
    }

    private function getFixtureProjectDir(): string
    {
        return __DIR__.'/../../Fixtures/assets';
    }
}
