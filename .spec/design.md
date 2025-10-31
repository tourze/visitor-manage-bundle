# Visitor Manage Bundle - 技术设计

## 技术概览

### 架构模式
- **扁平化 Service 层**：业务逻辑集中在 Service 层，不分层
- **贫血模型实体**：Entity 只包含数据和 getter/setter，不包含业务逻辑
- **依赖注入**：使用 Symfony DI 容器管理服务依赖
- **环境变量配置**：所有配置通过 $_ENV 读取，不创建 Configuration 类

### 核心设计原则
- **KISS**: 保持简单，优先可读性
- **YAGNI**: 只实现当前需要的功能
- **单一职责**: 每个 Service 有明确的职责边界
- **依赖倒置**: 通过接口定义依赖关系

### 技术决策理由
- 遵循 Monorepo 中 symfony-bundle-standards.md 要求
- 避免过度设计和不必要的抽象
- 优先业务逻辑清晰性和代码可维护性

## 公共API设计

### 核心服务接口

#### VisitorRegistrationService
```php
class VisitorRegistrationService
{
    public function register(VisitorRegistrationData $data): Visitor;
    public function updateVisitor(int $visitorId, VisitorRegistrationData $data): Visitor;
    public function getVisitor(int $visitorId): ?Visitor;
    public function deleteVisitor(int $visitorId): void;
}
```

#### VisitorInvitationService
```php
class VisitorInvitationService
{
    public function createInvitation(int $inviterId, VisitorRegistrationData $visitorData): VisitorInvitation;
    public function confirmInvitation(string $inviteCode): VisitorInvitation;
    public function rejectInvitation(string $inviteCode, string $reason): VisitorInvitation;
    public function cancelInvitation(int $invitationId): void;
    public function getInvitation(string $inviteCode): ?VisitorInvitation;
}
```

#### VisitorApprovalService
```php
class VisitorApprovalService
{
    public function submitForApproval(int $visitorId): VisitorApproval;
    public function approveVisitor(int $approvalId, int $approverId): VisitorApproval;
    public function rejectVisitor(int $approvalId, int $approverId, string $reason): VisitorApproval;
    public function getApproval(int $approvalId): ?VisitorApproval;
    public function getPendingApprovals(): array;
}
```

#### VisitorPassService
```php
class VisitorPassService
{
    public function generatePass(int $visitorId): VisitorPass;
    public function validatePass(string $passCode): ?VisitorPass;
    public function usePass(string $passCode): VisitorPass;
    public function getPass(string $passCode): ?VisitorPass;
    public function isPassValid(string $passCode): bool;
}
```

#### VisitorQueryService
```php
class VisitorQueryService
{
    public function searchVisitors(VisitorSearchCriteria $criteria): array;
    public function getVisitorsByStatus(string $status, int $page = 1, int $limit = 20): array;
    public function getVisitorStatistics(StatisticsOptions $options): array;
    public function getVisitorHistory(int $visitorId): array;
}
```

### 数据传输对象

#### VisitorRegistrationData
```php
class VisitorRegistrationData
{
    public string $name;
    public string $mobile;
    public string $company;
    public string $reason;
    public ?string $vehicleNumber;
    public \DateTime $appointmentTime;
    public ?int $bizUserId;
}
```

#### VisitorSearchCriteria
```php
class VisitorSearchCriteria
{
    public ?string $name;
    public ?string $mobile;
    public ?string $company;
    public ?\DateTime $appointmentFrom;
    public ?\DateTime $appointmentTo;
    public ?string $status;
    public int $page = 1;
    public int $limit = 20;
}
```

### 错误处理策略

#### 自定义异常
```php
class VisitorNotFoundException extends \Exception {}
class InvitationExpiredException extends \Exception {}
class ApprovalNotAuthorizedException extends \Exception {}
class PassExpiredException extends \Exception {}
class InvalidPassCodeException extends \Exception {}
class VisitorValidationException extends \Exception {}
```

#### 错误处理原则
- 所有业务异常继承自基础异常类
- Service 层抛出具体的业务异常
- 调用方负责处理异常并转换为适当的响应

## 内部架构

### 目录结构
```
src/
├── Entity/                 # 贫血模型实体
│   ├── Visitor.php
│   ├── VisitorInvitation.php
│   ├── VisitorApproval.php
│   ├── VisitorPass.php
│   └── VisitorLog.php
├── Repository/             # 数据访问
│   ├── VisitorRepository.php
│   ├── VisitorInvitationRepository.php
│   ├── VisitorApprovalRepository.php
│   ├── VisitorPassRepository.php
│   └── VisitorLogRepository.php
├── Service/                # 业务逻辑（扁平化）
│   ├── VisitorRegistrationService.php
│   ├── VisitorInvitationService.php
│   ├── VisitorApprovalService.php
│   ├── VisitorPassService.php
│   ├── VisitorQueryService.php
│   ├── VisitorValidationService.php
│   └── VisitorLogService.php
├── DTO/                    # 数据传输对象
│   ├── VisitorRegistrationData.php
│   ├── VisitorSearchCriteria.php
│   └── StatisticsOptions.php
├── Exception/              # 自定义异常
│   ├── VisitorNotFoundException.php
│   ├── InvitationExpiredException.php
│   ├── ApprovalNotAuthorizedException.php
│   ├── PassExpiredException.php
│   ├── InvalidPassCodeException.php
│   └── VisitorValidationException.php
├── Event/                  # 领域事件
│   ├── VisitorRegisteredEvent.php
│   ├── VisitorApprovedEvent.php
│   ├── VisitorRejectedEvent.php
│   └── PassUsedEvent.php
└── VisitorManageBundle.php # Bundle 入口
```

### 核心组件设计

#### 实体设计（贫血模型）
```php
// 示例：Visitor 实体
#[ORM\Entity(repositoryClass: VisitorRepository::class)]
class Visitor
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;
    
    #[ORM\ManyToOne(targetEntity: BizUser::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?BizUser $bizUser = null;
    
    #[ORM\Column(type: 'string')]
    private string $name;
    
    #[ORM\Column(type: 'string')]
    private string $mobile;
    
    #[ORM\Column(type: 'string')]
    private string $company;
    
    #[ORM\Column(type: 'text')]
    private string $reason;
    
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $vehicleNumber = null;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $appointmentTime;
    
    #[ORM\Column(type: 'string', enumType: VisitorStatus::class)]
    private VisitorStatus $status = VisitorStatus::PENDING;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $createdAt;
    
    #[ORM\Column(type: 'datetime')]
    private \DateTime $updatedAt;
    
    // 只包含 getter/setter 方法，不包含业务逻辑
}
```

#### Service 层业务逻辑
```php
// 示例：VisitorRegistrationService
class VisitorRegistrationService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VisitorRepository $visitorRepository,
        private readonly VisitorValidationService $validationService,
        private readonly VisitorLogService $logService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {}
    
    public function register(VisitorRegistrationData $data): Visitor
    {
        // 验证数据
        $this->validationService->validateRegistrationData($data);
        
        // 创建访客实体
        $visitor = new Visitor();
        $visitor->setName($data->name);
        $visitor->setMobile($data->mobile);
        $visitor->setCompany($data->company);
        $visitor->setReason($data->reason);
        $visitor->setVehicleNumber($data->vehicleNumber);
        $visitor->setAppointmentTime($data->appointmentTime);
        $visitor->setBizUser($data->bizUserId ? $this->getBizUser($data->bizUserId) : null);
        $visitor->setCreatedAt(new \DateTime());
        $visitor->setUpdatedAt(new \DateTime());
        
        // 持久化
        $this->entityManager->persist($visitor);
        $this->entityManager->flush();
        
        // 记录日志
        $this->logService->logAction($visitor, VisitorAction::REGISTERED);
        
        // 发布事件
        $this->eventDispatcher->dispatch(new VisitorRegisteredEvent($visitor));
        
        return $visitor;
    }
}
```

### 数据流设计

#### 访客登记流程
1. 调用 `VisitorRegistrationService::register()`
2. 数据验证 (`VisitorValidationService`)
3. 创建 Visitor 实体
4. 数据持久化 (`EntityManager`)
5. 记录操作日志 (`VisitorLogService`)
6. 发布领域事件 (`EventDispatcher`)

#### 审批流程
1. 调用 `VisitorApprovalService::submitForApproval()`
2. 验证访客状态和权限
3. 创建 VisitorApproval 实体
4. 发布待审批事件
5. 审批人操作后更新状态
6. 发布审批结果事件

#### 通行码生成流程
1. 访客审批通过后自动触发
2. 调用 `VisitorPassService::generatePass()`
3. 生成唯一通行码
4. 创建二维码内容（TODO: 集成二维码库）
5. 设置有效期
6. 持久化通行码信息

## 扩展机制

### 事件系统
基于 Symfony EventDispatcher 实现事件驱动架构：

```php
// 事件定义
class VisitorRegisteredEvent
{
    public function __construct(
        private readonly Visitor $visitor
    ) {}
    
    public function getVisitor(): Visitor
    {
        return $this->visitor;
    }
}

// 事件监听器示例
class VisitorEventListener
{
    public function onVisitorRegistered(VisitorRegisteredEvent $event): void
    {
        // 自动发送通知、触发审批流程等
    }
}
```

### 服务扩展点
通过依赖注入支持服务替换：

```php
// 接口定义
interface QrCodeGeneratorInterface
{
    public function generate(string $content): string;
}

// 默认实现（TODO）
class DefaultQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function generate(string $content): string
    {
        // TODO: 暂时返回文本，后续集成二维码库
        return $content;
    }
}

// 在服务中使用接口
class VisitorPassService
{
    public function __construct(
        private readonly QrCodeGeneratorInterface $qrCodeGenerator,
        // ...
    ) {}
}
```

### 配置扩展
所有配置通过环境变量读取，支持运行时配置：

```php
class VisitorPassService
{
    private readonly int $passValidityDays;
    private readonly bool $encryptionEnabled;
    
    public function __construct()
    {
        $this->passValidityDays = (int) ($_ENV['VISITOR_PASS_VALIDITY_DAYS'] ?? 7);
        $this->encryptionEnabled = filter_var(
            $_ENV['VISITOR_ENCRYPTION_ENABLED'] ?? 'false',
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
```

## 集成设计

### Symfony Bundle 集成

#### Bundle 配置
```php
class VisitorManageBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        // Bundle 初始化逻辑
    }
}
```

#### 服务自动装配
```yaml
# config/services.yaml （应用层配置，不在 Bundle 中创建）
services:
    _defaults:
        autowire: true
        autoconfigure: true
    
    Tourze\VisitorManageBundle\:
        resource: '../packages/visitor-manage-bundle/src/'
        exclude: '../packages/visitor-manage-bundle/src/{Entity,DTO,Exception}'
```

### BizUser Bundle 集成

#### 用户关联
```php
// 在服务中获取 BizUser
class VisitorRegistrationService
{
    public function __construct(
        private readonly BizUserRepository $bizUserRepository,
        // ...
    ) {}
    
    private function getBizUser(int $bizUserId): ?BizUser
    {
        return $this->bizUserRepository->find($bizUserId);
    }
}
```

#### 权限集成
```php
class VisitorApprovalService
{
    public function approveVisitor(int $approvalId, int $approverId): VisitorApproval
    {
        $approver = $this->bizUserRepository->find($approverId);
        if (!$approver) {
            throw new ApprovalNotAuthorizedException('审批人不存在');
        }
        
        // 验证审批权限（依赖 BizUser 的权限系统）
        if (!$this->hasApprovalPermission($approver)) {
            throw new ApprovalNotAuthorizedException('无审批权限');
        }
        
        // 执行审批逻辑
    }
}
```

### Doctrine ORM 集成

#### 实体映射
- 使用 PHP 属性进行 ORM 映射
- 支持 MySQL 和 PostgreSQL 数据库
- 自动生成数据库 migration

#### Repository 模式
```php
#[AsDoctrineRepository(Visitor::class)]
class VisitorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Visitor::class);
    }
    
    public function findByMobile(string $mobile): ?Visitor
    {
        return $this->findOneBy(['mobile' => $mobile]);
    }
    
    public function findPendingApprovals(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.status = :status')
            ->setParameter('status', VisitorStatus::PENDING)
            ->getQuery()
            ->getResult();
    }
}
```

## 测试策略

### 单元测试
- 每个 Service 类独立测试
- 使用 PHPUnit 和 mock 对象
- 覆盖所有业务逻辑分支
- 目标覆盖率：≥90%

### 集成测试
- 测试 Service 与 Repository 集成
- 测试 BizUser Bundle 集成
- 使用内存数据库进行测试
- 测试完整的业务流程

### 功能测试
- 端到端业务场景测试
- 事件系统测试
- 异常处理测试

### 测试示例
```php
class VisitorRegistrationServiceTest extends KernelTestCase
{
    private VisitorRegistrationService $service;
    
    protected function setUp(): void
    {
        $this->bootKernel();
        $this->service = static::getContainer()->get(VisitorRegistrationService::class);
    }
    
    public function testRegisterVisitor(): void
    {
        $data = new VisitorRegistrationData();
        $data->name = '张三';
        $data->mobile = '13800138000';
        $data->company = '测试公司';
        $data->reason = '商务洽谈';
        $data->appointmentTime = new \DateTime('+1 day');
        
        $visitor = $this->service->register($data);
        
        $this->assertInstanceOf(Visitor::class, $visitor);
        $this->assertEquals('张三', $visitor->getName());
        $this->assertEquals(VisitorStatus::PENDING, $visitor->getStatus());
    }
}
```

## 性能考虑

### 数据库优化
- 在常用查询字段添加索引
- 使用分页查询避免大量数据加载
- 优化复杂查询的性能

### 缓存策略（TODO）
- 访客统计数据缓存
- 通行码验证缓存
- 预留缓存接口扩展点

### 批量操作支持
```php
class VisitorRegistrationService
{
    public function batchRegister(array $registrationDataList): array
    {
        $visitors = [];
        
        foreach ($registrationDataList as $data) {
            $visitor = new Visitor();
            // 设置属性...
            $this->entityManager->persist($visitor);
            $visitors[] = $visitor;
        }
        
        // 批量提交减少数据库交互
        $this->entityManager->flush();
        
        return $visitors;
    }
}
```

## 安全考虑

### 数据加密（预留接口）
```php
interface EncryptionServiceInterface
{
    public function encrypt(string $data): string;
    public function decrypt(string $encryptedData): string;
}

// 默认实现（明文存储）
class PlaintextEncryptionService implements EncryptionServiceInterface
{
    public function encrypt(string $data): string
    {
        return $data; // 暂时明文存储
    }
    
    public function decrypt(string $encryptedData): string
    {
        return $encryptedData; // 暂时明文返回
    }
}
```

### 权限控制
- 集成 BizUser Bundle 权限系统
- 服务层进行权限验证
- 敏感操作记录操作日志

### 数据验证
```php
class VisitorValidationService
{
    public function validateRegistrationData(VisitorRegistrationData $data): void
    {
        if (empty($data->name)) {
            throw new VisitorValidationException('访客姓名不能为空');
        }
        
        if (!$this->isValidMobile($data->mobile)) {
            throw new VisitorValidationException('手机号格式不正确');
        }
        
        if ($data->appointmentTime < new \DateTime()) {
            throw new VisitorValidationException('预约时间不能早于当前时间');
        }
    }
    
    private function isValidMobile(string $mobile): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $mobile) === 1;
    }
}
```

## 错误处理

### 异常层次结构
```php
abstract class VisitorManageException extends \Exception {}

class VisitorNotFoundException extends VisitorManageException {}
class VisitorValidationException extends VisitorManageException {}
class InvitationExpiredException extends VisitorManageException {}
class ApprovalNotAuthorizedException extends VisitorManageException {}
class PassExpiredException extends VisitorManageException {}
```

### 错误日志记录
```php
class VisitorLogService
{
    public function logError(\Throwable $exception, ?Visitor $visitor = null): void
    {
        $log = new VisitorLog();
        $log->setVisitor($visitor);
        $log->setAction(VisitorAction::ERROR);
        $log->setRemark($exception->getMessage());
        $log->setCreatedAt(new \DateTime());
        
        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
```

## 部署和维护

### 环境变量配置
```bash
# 通行码配置
VISITOR_PASS_VALIDITY_DAYS=7
VISITOR_PASS_PREFIX="VP"

# 加密配置
VISITOR_ENCRYPTION_ENABLED=false
VISITOR_ENCRYPTION_KEY=""

# 二维码配置（TODO）
VISITOR_QR_CODE_SIZE=200
VISITOR_QR_CODE_FORMAT="png"
```

### 数据迁移
使用 Doctrine Migration 管理数据库结构变更：

```php
// migrations/VersionXXX.php
public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE visitor (
        id INT AUTO_INCREMENT NOT NULL,
        biz_user_id INT DEFAULT NULL,
        name VARCHAR(255) NOT NULL,
        mobile VARCHAR(20) NOT NULL,
        company VARCHAR(255) NOT NULL,
        reason LONGTEXT NOT NULL,
        vehicle_number VARCHAR(20) DEFAULT NULL,
        appointment_time DATETIME NOT NULL,
        status VARCHAR(20) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX IDX_VISITOR_BIZ_USER (biz_user_id),
        INDEX IDX_VISITOR_MOBILE (mobile),
        INDEX IDX_VISITOR_STATUS (status),
        INDEX IDX_VISITOR_APPOINTMENT_TIME (appointment_time),
        PRIMARY KEY(id)
    )');
}
```

### 监控和日志
- 所有重要操作记录到 VisitorLog
- 异常信息记录到系统日志
- 性能监控集成点预留

## 版本兼容性

### API 稳定性
- 公共 Service 接口保持向后兼容
- 新功能通过新方法添加
- 废弃方法保持一个主版本周期

### 数据库兼容性
- 数据库结构变更通过 Migration 管理
- 新字段设置默认值确保兼容性
- 删除字段前先标记为废弃

### 依赖版本
- PHP 8.1+ 
- Symfony 6.4+
- Doctrine ORM 2.14+
- biz-user-bundle 兼容版本

---

此设计遵循 Monorepo 的扁平化架构原则，优先代码简洁性和可维护性，为访客管理功能提供完整的业务逻辑封装，同时保持良好的扩展性和集成能力。