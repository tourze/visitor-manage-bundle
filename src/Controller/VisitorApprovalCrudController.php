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
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorApproval;
use Tourze\VisitorManageBundle\Enum\ApprovalStatus;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * 访客审批管理 CRUD 控制器
 *
 * @extends AbstractCrudController<VisitorApproval>
 */
#[AdminCrud(routePath: '/visitor-manage/approval', routeName: 'visitor_manage_approval')]
final class VisitorApprovalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorApproval::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访客审批')
            ->setEntityLabelInPlural('访客审批管理')
            ->setSearchFields(['visitor.name', 'visitor.mobile', 'visitor.company'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理访客审批记录，包括审批状态、审批人、审批时间等信息')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield AssociationField::new('visitor', '访客信息')
            ->setRequired(true)
            ->setColumns(6)
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
                        '<div><strong>%s</strong><br><small class="text-muted">%s | %s</small></div>',
                        htmlspecialchars($value->getName()),
                        htmlspecialchars($value->getMobile()),
                        htmlspecialchars($value->getCompany())
                    );
                }

                return $value;
            })
        ;

        yield ChoiceField::new('status', '审批状态')
            ->setChoices([
                '待审批' => ApprovalStatus::PENDING,
                '已通过' => ApprovalStatus::APPROVED,
                '已拒绝' => ApprovalStatus::REJECTED,
            ])
            ->setRequired(true)
            ->setColumns(6)
            ->formatValue(function ($value, $entity) {
                if ($value instanceof ApprovalStatus) {
                    $badgeClass = match ($value) {
                        ApprovalStatus::PENDING => 'warning',
                        ApprovalStatus::APPROVED => 'success',
                        ApprovalStatus::REJECTED => 'danger',
                    };

                    return sprintf('<span class="badge badge-%s">%s</span>', $badgeClass, $value->getLabel());
                }

                return $value;
            })
        ;

        yield IntegerField::new('approver', '审批人ID')
            ->setColumns(6)
            ->setHelp('审批人的用户ID')
            ->hideOnIndex()
        ;

        yield TextareaField::new('rejectReason', '拒绝原因')
            ->setColumns(12)
            ->setHelp('当审批状态为拒绝时，必须填写拒绝原因')
            ->hideOnIndex()
            ->setFormTypeOptions([
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => '请输入拒绝原因...',
                ],
            ])
        ;

        yield DateTimeField::new('approveTime', '审批时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('审批操作的时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->hideOnForm()
        ;

        // 在详情页显示访客的详细信息
        if (Crud::PAGE_DETAIL === $pageName) {
            yield AssociationField::new('visitor', '访客详细信息')
                ->setTemplatePath('admin/visitor_approval/visitor_detail.html.twig')
            ;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('visitor', '访客')
                ->setFormTypeOptions([
                    'choice_label' => function (Visitor $visitor) {
                        return sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile());
                    },
                ])
            )
            ->add(ChoiceFilter::new('status', '审批状态')
                ->setChoices([
                    '待审批' => ApprovalStatus::PENDING,
                    '已通过' => ApprovalStatus::APPROVED,
                    '已拒绝' => ApprovalStatus::REJECTED,
                ])
            )
            ->add(NumericFilter::new('approver', '审批人ID'))
            ->add(DateTimeFilter::new('approveTime', '审批时间'))
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
}
