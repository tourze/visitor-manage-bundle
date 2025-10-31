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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Enum\VisitorStatus;

/**
 * 访客管理 CRUD 控制器
 *
 * @extends AbstractCrudController<Visitor>
 */
#[AdminCrud(routePath: '/visitor-manage/visitor', routeName: 'visitor_manage_visitor')]
final class VisitorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Visitor::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访客')
            ->setEntityLabelInPlural('访客管理')
            ->setSearchFields(['name', 'mobile', 'company', 'contactPerson', 'idCard'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理访客信息，包括访客基本信息、预约时间、审批状态等')
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

        yield TextField::new('name', '访客姓名')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('访客的真实姓名')
        ;

        yield TextField::new('mobile', '手机号码')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('访客的手机号码，用于联系和身份验证')
        ;

        yield TextField::new('company', '公司名称')
            ->setColumns(6)
            ->setHelp('访客所在的公司或组织')
        ;

        yield TextField::new('contactPerson', '联系人')
            ->setColumns(6)
            ->setHelp('内部联系人姓名')
            ->hideOnIndex()
        ;

        yield TextField::new('idCard', '身份证号')
            ->setColumns(6)
            ->setHelp('访客身份证号码（选填）')
            ->hideOnIndex()
        ;

        yield TextField::new('vehicleNumber', '车牌号')
            ->setColumns(6)
            ->setHelp('访客车辆车牌号（选填）')
            ->hideOnIndex()
        ;

        yield IntegerField::new('bizUserId', '关联用户ID')
            ->setColumns(6)
            ->setHelp('关联的业务用户ID（选填）')
            ->hideOnIndex()
        ;

        yield TextareaField::new('reason', '来访事由')
            ->setRequired(true)
            ->setColumns(12)
            ->setHelp('详细说明来访目的和事由')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('appointmentTime', '预约时间')
            ->setRequired(true)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('访客预约的来访时间')
            ->hideOnIndex()
        ;

        yield ChoiceField::new('status', '访客状态')
            ->setChoices([
                '待审批' => VisitorStatus::PENDING,
                '已审批' => VisitorStatus::APPROVED,
                '已拒绝' => VisitorStatus::REJECTED,
                '已签入' => VisitorStatus::SIGNED_IN,
                '已签出' => VisitorStatus::SIGNED_OUT,
                '已取消' => VisitorStatus::CANCELLED,
            ])
            ->setRequired(true)
            ->setColumns(6)
            ->formatValue(function ($value, $entity) {
                if ($value instanceof VisitorStatus) {
                    $badgeClass = match ($value) {
                        VisitorStatus::PENDING => 'warning',
                        VisitorStatus::APPROVED => 'success',
                        VisitorStatus::REJECTED => 'danger',
                        VisitorStatus::SIGNED_IN => 'info',
                        VisitorStatus::SIGNED_OUT => 'secondary',
                        VisitorStatus::CANCELLED => 'dark',
                    };

                    return sprintf('<span class="badge badge-%s">%s</span>', $badgeClass, $value->getLabel());
                }

                return $value;
            })
        ;

        yield DateTimeField::new('signInTime', '签到时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('访客实际签到时间')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('signOutTime', '签退时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('访客实际签退时间')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnIndex()
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnIndex()
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '访客姓名'))
            ->add(TextFilter::new('mobile', '手机号码'))
            ->add(TextFilter::new('company', '公司名称'))
            ->add(TextFilter::new('contactPerson', '联系人'))
            ->add(ChoiceFilter::new('status', '访客状态')
                ->setChoices([
                    '待审批' => VisitorStatus::PENDING,
                    '已审批' => VisitorStatus::APPROVED,
                    '已拒绝' => VisitorStatus::REJECTED,
                    '已签入' => VisitorStatus::SIGNED_IN,
                    '已签出' => VisitorStatus::SIGNED_OUT,
                    '已取消' => VisitorStatus::CANCELLED,
                ])
            )
            ->add(DateTimeFilter::new('appointmentTime', '预约时间'))
            ->add(DateTimeFilter::new('signInTime', '签到时间'))
            ->add(DateTimeFilter::new('signOutTime', '签退时间'))
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

        // 默认按创建时间倒序排列
        $qb->orderBy('entity.createTime', 'DESC');

        return $qb;
    }
}
