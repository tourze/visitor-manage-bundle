<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorPass;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * 访客通行码管理 CRUD 控制器
 *
 * @extends AbstractCrudController<VisitorPass>
 */
#[AdminCrud(routePath: '/visitor-manage/pass', routeName: 'visitor_manage_pass')]
final class VisitorPassCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorPass::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访客通行码')
            ->setEntityLabelInPlural('访客通行码管理')
            ->setSearchFields(['passCode', 'visitor.name', 'visitor.mobile'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理访客通行码，包括通行码生成、有效期管理、使用记录等')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateQr = Action::new('generateQr', '生成二维码')
            ->linkToCrudAction('generateQrCode')
            ->setIcon('fa fa-qrcode')
            ->displayIf(static function ($entity) {
                return $entity instanceof VisitorPass && '' === $entity->getQrCode();
            })
        ;

        $viewQr = Action::new('viewQr', '查看二维码')
            ->linkToCrudAction('viewQrCode')
            ->setIcon('fa fa-eye')
            ->displayIf(static function ($entity) {
                return $entity instanceof VisitorPass && '' !== $entity->getQrCode();
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $generateQr)
            ->add(Crud::PAGE_DETAIL, $viewQr)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield from $this->getBasicFields();
        yield from $this->getTimeFields();
        yield from $this->getStatusFields();
        yield from $this->getContentFields();

        if (Crud::PAGE_DETAIL === $pageName) {
            yield from $this->getDetailFields();
        }
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getBasicFields(): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('visitor', '访客信息')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOptions($this->getVisitorFormOptions())
            ->formatValue($this->getVisitorFormatter())
        ;

        yield TextField::new('passCode', '通行码')
            ->setColumns(6)
            ->setHelp('系统生成的唯一通行码')
            ->formatValue($this->getPassCodeFormatter())
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getTimeFields(): iterable
    {
        yield DateTimeField::new('validStartTime', '有效开始时间')
            ->setRequired(true)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('通行码的有效开始时间')
        ;

        yield DateTimeField::new('validEndTime', '有效结束时间')
            ->setRequired(true)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('通行码的有效结束时间')
            ->formatValue($this->getValidEndTimeFormatter())
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->hideOnForm()
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getStatusFields(): iterable
    {
        yield BooleanField::new('isUsed', '是否已使用')
            ->setColumns(3)
            ->formatValue($this->getUsageStatusFormatter())
            ->onlyOnIndex()
        ;

        yield DateTimeField::new('useTime', '使用时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('通行码的实际使用时间')
            ->hideOnForm()
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getContentFields(): iterable
    {
        yield TextareaField::new('qrCode', '二维码内容')
            ->setColumns(12)
            ->setHelp('二维码的Base64编码内容或URL')
            ->hideOnIndex()
            ->setFormTypeOptions([
                'attr' => [
                    'rows' => 3,
                    'placeholder' => '系统自动生成二维码内容...',
                ],
            ])
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function getDetailFields(): iterable
    {
        yield AssociationField::new('visitor', '访客详细信息')
            ->setTemplatePath('admin/visitor_pass/visitor_detail.html.twig')
        ;

        yield TextareaField::new('qrCode', '二维码预览')
            ->setTemplatePath('admin/visitor_pass/qr_code_preview.html.twig')
            ->hideOnIndex()
        ;
    }

    /**
     * @return array<string, mixed>
     */
    private function getVisitorFormOptions(): array
    {
        return [
            'choice_label' => static function (Visitor $visitor) {
                return sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile());
            },
            'query_builder' => static function (VisitorRepository $repository) {
                return $repository->createQueryBuilder('v')
                    ->where('v.status = :status')
                    ->setParameter('status', 'approved')
                    ->orderBy('v.createTime', 'DESC')
                ;
            },
        ];
    }

    private function getVisitorFormatter(): callable
    {
        return static function ($value, $entity) {
            if ($value instanceof Visitor) {
                return sprintf(
                    '<div><strong>%s</strong><br><small class="text-muted">%s | %s</small></div>',
                    htmlspecialchars($value->getName()),
                    htmlspecialchars($value->getMobile()),
                    htmlspecialchars($value->getCompany())
                );
            }

            return $value;
        };
    }

    private function getPassCodeFormatter(): callable
    {
        return static function ($value, $entity) {
            if ($value) {
                $stringValue = match (true) {
                    is_string($value) => $value,
                    is_scalar($value) => (string) $value,
                    default => '',
                };

                return sprintf(
                    '<code class="text-primary" style="font-size: 1.1em; font-weight: bold;">%s</code>',
                    htmlspecialchars($stringValue)
                );
            }

            return '<span class="text-muted">未生成</span>';
        };
    }

    private function getValidEndTimeFormatter(): callable
    {
        return static function ($value, $entity) {
            if ($value instanceof \DateTimeInterface) {
                $now = new \DateTimeImmutable();
                $isExpired = $value < $now;
                $class = $isExpired ? 'text-danger' : 'text-success';
                $status = $isExpired ? '（已过期）' : '（有效）';

                return sprintf(
                    '<span class="%s">%s %s</span>',
                    $class,
                    $value->format('Y-m-d H:i:s'),
                    $status
                );
            }

            return $value;
        };
    }

    private function getUsageStatusFormatter(): callable
    {
        return static function ($value, $entity) {
            if ($entity instanceof VisitorPass) {
                $useTime = $entity->getUseTime();
                if ($useTime instanceof \DateTimeInterface) {
                    return sprintf(
                        '<span class="badge badge-info">已使用</span><br><small class="text-muted">%s</small>',
                        $useTime->format('Y-m-d H:i:s')
                    );
                }
            }

            return '<span class="badge badge-secondary">未使用</span>';
        };
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('passCode', '通行码'))
            ->add(EntityFilter::new('visitor', '访客')
                ->setFormTypeOptions([
                    'choice_label' => function (Visitor $visitor) {
                        return sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile());
                    },
                ])
            )
            ->add(BooleanFilter::new('isUsed', '是否已使用')
                ->setFormTypeOptions([
                    'choice_label' => function ($value) {
                        return $value ? '已使用' : '未使用';
                    },
                ])
            )
            ->add(DateTimeFilter::new('validStartTime', '有效开始时间'))
            ->add(DateTimeFilter::new('validEndTime', '有效结束时间'))
            ->add(DateTimeFilter::new('useTime', '使用时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // 联接访客表以支持搜索
        $qb->leftJoin('entity.visitor', 'v')
            ->addSelect('v')
        ;

        // 默认按创建时间倒序排列
        $qb->orderBy('entity.createTime', 'DESC');

        return $qb;
    }

    /**
     * 生成二维码的自定义动作
     */
    #[AdminAction(routeName: 'visitor_pass_generate_qr', routePath: '/visitor-pass/generate-qr')]
    public function generateQrCode(): void
    {
        // 这里可以添加生成二维码的逻辑
        // 由于这是一个演示控制器，实际的二维码生成逻辑应该在服务层实现
        $this->addFlash('success', '二维码生成功能需要在服务层实现');
    }

    /**
     * 查看二维码的自定义动作
     */
    #[AdminAction(routeName: 'visitor_pass_view_qr', routePath: '/visitor-pass/view-qr')]
    public function viewQrCode(): void
    {
        // 这里可以添加查看二维码的逻辑
        // 可以跳转到一个专门显示二维码的页面
        $this->addFlash('info', '二维码查看功能需要在服务层实现');
    }
}
