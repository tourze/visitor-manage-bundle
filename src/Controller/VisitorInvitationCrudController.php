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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\VisitorManageBundle\Entity\Visitor;
use Tourze\VisitorManageBundle\Entity\VisitorInvitation;
use Tourze\VisitorManageBundle\Enum\InvitationStatus;
use Tourze\VisitorManageBundle\Repository\VisitorRepository;

/**
 * 访客邀请管理 CRUD 控制器
 *
 * @extends AbstractCrudController<VisitorInvitation>
 */
#[AdminCrud(routePath: '/visitor-manage/invitation', routeName: 'visitor_manage_invitation')]
final class VisitorInvitationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorInvitation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('访客邀请')
            ->setEntityLabelInPlural('访客邀请管理')
            ->setSearchFields(['inviteCode', 'visitor.name', 'visitor.mobile', 'visitor.company'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理访客邀请记录，包括邀请码、邀请状态、过期时间等信息')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateCode = Action::new('generateCode', '生成邀请码')
            ->linkToCrudAction('generateInviteCode')
            ->setIcon('fa fa-qrcode')
            ->displayIf(static function ($entity) {
                return $entity instanceof VisitorInvitation
                       && InvitationStatus::PENDING === $entity->getStatus();
            })
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $generateCode)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex()
        ;

        yield IntegerField::new('inviter', '邀请者ID')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('邀请者的用户ID')
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
            ->formatValue($this->formatVisitorValue(...))
        ;

        yield TextField::new('inviteCode', '邀请码')
            ->setColumns(6)
            ->setHelp('系统生成的唯一邀请码')
            ->hideOnIndex()
            ->formatValue($this->formatInviteCodeValue(...))
        ;

        yield ChoiceField::new('status', '邀请状态')
            ->setChoices([
                '待确认' => InvitationStatus::PENDING,
                '已确认' => InvitationStatus::CONFIRMED,
                '已拒绝' => InvitationStatus::REJECTED,
                '已过期' => InvitationStatus::EXPIRED,
            ])
            ->setRequired(true)
            ->setColumns(6)
            ->formatValue($this->formatStatusValue(...))
        ;

        yield DateTimeField::new('expireTime', '过期时间')
            ->setRequired(true)
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->setHelp('邀请码的过期时间')
            ->hideOnIndex()
            ->formatValue($this->formatExpireTimeValue(...))
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setColumns(6)
            ->hideOnForm()
        ;

        // 在详情页显示访客的详细信息
        if (Crud::PAGE_DETAIL === $pageName) {
            yield AssociationField::new('visitor', '访客详细信息')
                ->setTemplatePath('admin/visitor_invitation/visitor_detail.html.twig')
            ;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('inviteCode', '邀请码'))
            ->add(NumericFilter::new('inviter', '邀请者ID'))
            ->add(EntityFilter::new('visitor', '访客')
                ->setFormTypeOptions([
                    'choice_label' => function (Visitor $visitor) {
                        return sprintf('%s (%s)', $visitor->getName(), $visitor->getMobile());
                    },
                ])
            )
            ->add(ChoiceFilter::new('status', '邀请状态')
                ->setChoices([
                    '待确认' => InvitationStatus::PENDING,
                    '已确认' => InvitationStatus::CONFIRMED,
                    '已拒绝' => InvitationStatus::REJECTED,
                    '已过期' => InvitationStatus::EXPIRED,
                ])
            )
            ->add(DateTimeFilter::new('expireTime', '过期时间'))
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
     * 生成邀请码的自定义动作
     */
    #[AdminAction(routeName: 'visitor_invitation_generate_code', routePath: '/visitor-invitation/generate-code')]
    public function generateInviteCode(): void
    {
        // 这里可以添加生成邀请码的逻辑
        // 由于这是一个演示控制器，实际的业务逻辑应该在服务层实现
        $this->addFlash('success', '邀请码生成功能需要在服务层实现');
    }

    private function formatVisitorValue(mixed $value, mixed $entity): mixed
    {
        if ($value instanceof Visitor) {
            return sprintf(
                '<div><strong>%s</strong><br><small class="text-muted">%s | %s</small></div>',
                htmlspecialchars($value->getName()),
                htmlspecialchars($value->getMobile()),
                htmlspecialchars($value->getCompany())
            );
        }

        return $value;
    }

    private function formatInviteCodeValue(mixed $value, mixed $entity): mixed
    {
        if (null !== $value && '' !== $value) {
            assert(is_string($value), 'inviteCode should be a string');

            return sprintf(
                '<code class="text-primary" style="font-size: 1.1em; font-weight: bold;">%s</code>',
                htmlspecialchars($value)
            );
        }

        return '<span class="text-muted">未生成</span>';
    }

    private function formatStatusValue(mixed $value, mixed $entity): mixed
    {
        if ($value instanceof InvitationStatus) {
            $badgeClass = match ($value) {
                InvitationStatus::PENDING => 'warning',
                InvitationStatus::CONFIRMED => 'success',
                InvitationStatus::REJECTED => 'danger',
                InvitationStatus::EXPIRED => 'secondary',
            };

            return sprintf('<span class="badge badge-%s">%s</span>', $badgeClass, $value->getLabel());
        }

        return $value;
    }

    private function formatExpireTimeValue(mixed $value, mixed $entity): mixed
    {
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
    }
}
