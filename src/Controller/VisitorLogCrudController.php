<?php

declare(strict_types=1);

namespace Tourze\VisitorManageBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorLog;
use Tourze\VisitorManageBundle\Enum\VisitorAction;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * 访客日志管理 CRUD 控制器
 *
 * @extends AbstractCrudController<VisitorLog>
 */
#[AdminCrud(routePath: '/visitor-manage/log', routeName: 'visitor_manage_log')]
final class VisitorLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访客日志')
            ->setEntityLabelInPlural('访客日志管理')
            ->setSearchFields(['visitor.name', 'visitor.mobile', 'remark'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->setHelp('index', '查看访客操作日志记录，包括注册、审批、签入签出等各种操作')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // 日志通常只允许查看，不允许编辑、删除和新建
            ->disable(Action::EDIT, Action::DELETE, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setIcon('fa fa-eye')->setLabel('查看详情');
            })
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('visitor', '访客信息')
            ->setColumns(4)
            ->setFormTypeOptions([
                'choice_label' => function (Visitor $visitor) {
                    return sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile());
                },
                'query_builder' => function (VisitorRepository $repository) {
                    return $repository->createQueryBuilder('v')
                        ->orderBy('v.createTime', 'DESC')
                    ;
                },
            ])
            ->formatValue(function ($value, $entity) {
                if ($value instanceof Visitor) {
                    return sprintf(
                        '<div><strong>%s</strong><br><small class="text-muted">%s</small></div>',
                        htmlspecialchars($value->getName()),
                        htmlspecialchars($value->getMobile())
                    );
                }

                return null !== $value ? $value : '<span class="text-muted">系统操作</span>';
            })
        ;

        yield ChoiceField::new('action', '操作类型')
            ->setChoices([
                '已注册' => VisitorAction::REGISTERED,
                '已审批' => VisitorAction::APPROVED,
                '已拒绝' => VisitorAction::REJECTED,
                '已签入' => VisitorAction::SIGNED_IN,
                '已签出' => VisitorAction::SIGNED_OUT,
                '已取消' => VisitorAction::CANCELLED,
                '批量审批' => VisitorAction::BULK_APPROVED,
                '批量拒绝' => VisitorAction::BULK_REJECTED,
                '错误记录' => VisitorAction::ERROR,
                '通行证已生成' => VisitorAction::PASS_GENERATED,
                '通行证已使用' => VisitorAction::PASS_USED,
                '其他操作' => VisitorAction::OTHER,
            ])
            ->setRequired(true)
            ->setColumns(3)
            ->formatValue(function ($value, $entity) {
                if ($value instanceof VisitorAction) {
                    $badgeClass = match ($value) {
                        VisitorAction::REGISTERED => 'primary',
                        VisitorAction::APPROVED => 'success',
                        VisitorAction::REJECTED => 'danger',
                        VisitorAction::SIGNED_IN => 'info',
                        VisitorAction::SIGNED_OUT => 'secondary',
                        VisitorAction::CANCELLED => 'dark',
                        VisitorAction::BULK_APPROVED => 'success',
                        VisitorAction::BULK_REJECTED => 'danger',
                        VisitorAction::PASS_GENERATED => 'info',
                        VisitorAction::PASS_USED => 'primary',
                        VisitorAction::ERROR => 'warning',
                        default => 'light',
                    };

                    return sprintf('<span class="badge badge-%s">%s</span>', $badgeClass, $value->getLabel());
                }

                return $value;
            })
        ;

        yield IntegerField::new('operator', '操作人ID')
            ->setColumns(2)
            ->setHelp('执行操作的用户ID')
            ->formatValue(function ($value, $entity) {
                return null !== $value ? $value : '<span class="text-muted">系统</span>';
            })
        ;

        yield DateTimeField::new('createTime', '操作时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(3)
            ->hideOnForm()
        ;

        // 备注字段隐藏在Index页面，只在详情和表单中显示
        yield TextareaField::new('remark', '备注说明')
            ->setColumns(6)
            ->setHelp('操作的详细说明或备注信息')
            ->hideOnIndex()
            ->setFormTypeOptions([
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'readonly' => true,
                    'placeholder' => '请输入备注信息...',
                ],
            ])
        ;

        // 在详情页显示完整信息
        if (Crud::PAGE_DETAIL === $pageName) {
            yield AssociationField::new('visitor', '访客详细信息')
                ->setTemplatePath('admin/visitor_log/visitor_detail.html.twig')
            ;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('visitor', '访客')
                ->setFormTypeOptions([
                    'choice_label' => function (?Visitor $visitor) {
                        return null !== $visitor ? sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile()) : '系统操作';
                    },
                ])
            )
            ->add(ChoiceFilter::new('action', '操作类型')
                ->setChoices([
                    '已注册' => VisitorAction::REGISTERED,
                    '已审批' => VisitorAction::APPROVED,
                    '已拒绝' => VisitorAction::REJECTED,
                    '已签入' => VisitorAction::SIGNED_IN,
                    '已签出' => VisitorAction::SIGNED_OUT,
                    '已取消' => VisitorAction::CANCELLED,
                    '批量审批' => VisitorAction::BULK_APPROVED,
                    '批量拒绝' => VisitorAction::BULK_REJECTED,
                    '错误记录' => VisitorAction::ERROR,
                    '通行证已生成' => VisitorAction::PASS_GENERATED,
                    '通行证已使用' => VisitorAction::PASS_USED,
                    '其他操作' => VisitorAction::OTHER,
                ])
            )
            ->add(NumericFilter::new('operator', '操作人ID'))
            ->add(TextFilter::new('remark', '备注说明'))
            ->add(DateTimeFilter::new('createTime', '操作时间'))
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

        // 默认按创建时间倒序排列，显示最新的日志
        $qb->orderBy('entity.createTime', 'DESC');

        return $qb;
    }
}
