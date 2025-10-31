# Visitor Manage Bundle - TDD实施任务

## 任务概览

基于需求规范和技术设计，使用TDD方法论实施 visitor-manage-bundle 包。任务遵循红-绿-重构循环，确保代码质量和测试覆盖率达标。

### 实施原则
- **测试先行**：每个功能先写测试，后写实现
- **小步迭代**：每个任务聚焦单一功能点
- **质量门禁**：每个任务完成必须通过 PHPStan Level 8 和 PHPUnit 测试
- **零容忍**：禁止跳过测试、无效断言、空实现等偷懒行为

### 目标覆盖率
- **Package单元测试覆盖率**: ≥90%
- **集成测试**: 覆盖所有主要业务流程
- **功能测试**: 完整的端到端场景

## 第一阶段：基础设施(Infrastructure)

### TDD-001: [RED] 创建Visitor实体测试
**依赖**: []
**估时**: 30分钟
**类型**: RED

**描述**:
1. 创建 `tests/Entity/VisitorTest.php`
2. 编写Visitor实体的基础测试用例
   - 测试实体创建和属性设置
   - 测试getter/setter方法
   - 测试默认值和必填字段
3. 编写实体验证测试（Symfony Validator）

**验收标准**:
- [ ] 测试运行失败（红色状态）
- [ ] 测试覆盖所有Visitor实体属性
- [ ] 测试代码通过PHPStan检查
- [ ] 包含边界条件测试（空值、极长字符串等）

**测试场景**:
```php
- testVisitorCreation() // 基础创建
- testGettersAndSetters() // 所有属性的getter/setter
- testDefaultValues() // 默认状态值
- testBizUserRelation() // BizUser关联关系
- testAppointmentTimeValidation() // 预约时间验证
```

---

### TDD-002: [GREEN] 实现Visitor实体
**依赖**: [TDD-001]
**估时**: 45分钟
**类型**: GREEN

**描述**:
1. 创建 `src/Entity/Visitor.php`
2. 实现贫血模型实体（只包含数据和getter/setter）
3. 添加Doctrine ORM映射注解
4. 创建VisitorStatus枚举类
5. 确保所有测试通过

**验收标准**:
- [ ] 所有TDD-001测试通过（绿色状态）
- [ ] 实体通过PHPStan Level 8检查
- [ ] 不包含业务逻辑（纯贫血模型）
- [ ] Doctrine映射正确配置

**实现要点**:
- 使用PHP 8.1+属性语法进行ORM映射
- 创建VisitorStatus枚举（PENDING/APPROVED/REJECTED/SIGNED_IN/SIGNED_OUT）
- 所有必填字段添加适当的验证约束

---

### TDD-003: [REFACTOR] 优化Visitor实体设计
**依赖**: [TDD-002]
**估时**: 20分钟
**类型**: REFACTOR

**描述**:
1. 重构Visitor实体代码结构
2. 优化字段类型和约束
3. 添加实体文档注释
4. 确保代码风格符合PSR-12

**验收标准**:
- [ ] 所有测试仍然通过
- [ ] 代码可读性提升
- [ ] 类型声明更加精确
- [ ] 文档注释完整

---

### TDD-004: [RED] 创建其他实体测试
**依赖**: [TDD-003]
**估时**: 60分钟
**类型**: RED

**描述**:
1. 创建 `VisitorInvitation` 实体测试
2. 创建 `VisitorApproval` 实体测试
3. 创建 `VisitorPass` 实体测试
4. 创建 `VisitorLog` 实体测试
5. 测试实体间关联关系

**验收标准**:
- [ ] 所有实体测试失败（红色状态）
- [ ] 测试覆盖实体间关联关系
- [ ] 包含级联操作测试
- [ ] 枚举类型测试完整

**测试场景**:
```php
VisitorInvitationTest:
- testInvitationCreation()
- testInviterRelation() // 与BizUser关联
- testVisitorRelation() // 与Visitor关联
- testInviteCodeGeneration()
- testStatusTransitions()

VisitorApprovalTest:
- testApprovalCreation()
- testApproverRelation()
- testStatusValidation()

VisitorPassTest:
- testPassCreation()
- testPassCodeUniqueness()
- testValidityPeriod()

VisitorLogTest:
- testLogCreation()
- testActionTypes()
- testOperatorRelation()
```

---

### TDD-005: [GREEN] 实现其他实体
**依赖**: [TDD-004]
**估时**: 90分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorInvitation` 实体
2. 实现 `VisitorApproval` 实体
3. 实现 `VisitorPass` 实体
4. 实现 `VisitorLog` 实体
5. 创建相关枚举类
6. 确保所有测试通过

**验收标准**:
- [ ] 所有实体测试通过
- [ ] 实体关联关系正确
- [ ] 贫血模型设计（无业务逻辑）
- [ ] PHPStan Level 8 零错误

**实现要点**:
- 创建InvitationStatus、ApprovalStatus、VisitorAction等枚举
- 正确配置实体间的关联关系
- 添加适当的索引和约束

---

### TDD-006: [REFACTOR] 优化实体关系设计
**依赖**: [TDD-005]
**估时**: 30分钟
**类型**: REFACTOR

**描述**:
1. 优化实体间关联关系配置
2. 重构枚举类设计
3. 优化数据库索引配置
4. 统一实体代码风格

**验收标准**:
- [ ] 所有测试通过
- [ ] 关联关系更加清晰
- [ ] 数据库性能优化
- [ ] 代码结构统一

---

### TDD-007: [RED] 创建Repository测试
**依赖**: [TDD-006]
**估时**: 45分钟
**类型**: RED

**描述**:
1. 创建 `VisitorRepositoryTest`
2. 创建其他Repository测试
3. 编写数据查询方法测试
4. 编写分页查询测试

**验收标准**:
- [ ] Repository测试失败（红色状态）
- [ ] 测试覆盖常用查询方法
- [ ] 包含分页和排序测试
- [ ] 性能相关查询测试

**测试场景**:
```php
VisitorRepositoryTest:
- testFindByMobile()
- testFindByStatus()
- testFindByAppointmentDateRange()
- testFindPendingApprovals()
- testFindWithPagination()
- testCountByStatus()

VisitorInvitationRepositoryTest:
- testFindByInviteCode()
- testFindExpiredInvitations()
- testFindByInviter()

VisitorPassRepositoryTest:
- testFindByPassCode()
- testFindValidPasses()
- testFindUsedPasses()
```

---

### TDD-008: [GREEN] 实现Repository类
**依赖**: [TDD-007]
**估时**: 60分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorRepository`
2. 实现其他Repository类
3. 实现自定义查询方法
4. 确保所有测试通过

**验收标准**:
- [ ] 所有Repository测试通过
- [ ] 继承ServiceEntityRepository
- [ ] 查询性能优化（使用QueryBuilder）
- [ ] PHPStan检查通过

**实现要点**:
- 使用Doctrine QueryBuilder构建复杂查询
- 为常用查询字段添加索引
- 实现分页查询支持

---

### TDD-009: [REFACTOR] 优化Repository查询
**依赖**: [TDD-008]
**估时**: 25分钟
**类型**: REFACTOR

**描述**:
1. 优化查询性能
2. 重构复杂查询方法
3. 添加查询缓存策略（预留）
4. 统一命名约定

**验收标准**:
- [ ] 所有测试通过
- [ ] 查询性能优化
- [ ] 代码可读性提升
- [ ] 命名规范统一

---

## 第二阶段：DTO和异常类

### TDD-010: [RED] 创建DTO测试
**依赖**: [TDD-009]
**估时**: 30分钟
**类型**: RED

**描述**:
1. 创建 `VisitorRegistrationDataTest`
2. 创建 `VisitorSearchCriteriaTest`
3. 创建 `StatisticsOptionsTest`
4. 测试DTO数据验证

**验收标准**:
- [ ] DTO测试失败（红色状态）
- [ ] 测试数据绑定和验证
- [ ] 测试默认值设置
- [ ] 包含边界条件测试

**测试场景**:
```php
VisitorRegistrationDataTest:
- testDataBinding()
- testValidation()
- testMobileFormatValidation()
- testAppointmentTimeValidation()

VisitorSearchCriteriaTest:
- testSearchCriteriaCreation()
- testPaginationDefaults()
- testDateRangeValidation()
```

---

### TDD-011: [GREEN] 实现DTO类
**依赖**: [TDD-010]
**估时**: 40分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorRegistrationData`
2. 实现 `VisitorSearchCriteria`
3. 实现 `StatisticsOptions`
4. 添加Symfony Validator约束

**验收标准**:
- [ ] 所有DTO测试通过
- [ ] 数据验证规则正确
- [ ] 类型声明完整
- [ ] PHPStan检查通过

---

### TDD-012: [RED] 创建异常类测试
**依赖**: [TDD-011]
**估时**: 20分钟
**类型**: RED

**描述**:
1. 创建自定义异常类测试
2. 测试异常继承关系
3. 测试异常消息和代码

**验收标准**:
- [ ] 异常测试失败（红色状态）
- [ ] 测试异常层次结构
- [ ] 测试国际化消息支持

---

### TDD-013: [GREEN] 实现异常类
**依赖**: [TDD-012]
**估时**: 30分钟
**类型**: GREEN

**描述**:
1. 实现异常基类和子类
2. 创建异常层次结构
3. 添加错误代码定义

**验收标准**:
- [ ] 所有异常测试通过
- [ ] 异常继承关系正确
- [ ] 错误消息清晰

---

## 第三阶段：核心业务服务

### TDD-014: [RED] 创建VisitorValidationService测试
**依赖**: [TDD-013]
**估时**: 40分钟
**类型**: RED

**描述**:
1. 创建 `VisitorValidationServiceTest`
2. 编写数据验证测试用例
3. 编写业务规则验证测试
4. 编写异常场景测试

**验收标准**:
- [ ] 验证服务测试失败（红色状态）
- [ ] 覆盖所有验证规则
- [ ] 测试异常抛出场景
- [ ] 包含边界条件测试

**测试场景**:
```php
- testValidateRegistrationData_Success()
- testValidateRegistrationData_EmptyName()
- testValidateRegistrationData_InvalidMobile()
- testValidateRegistrationData_PastAppointmentTime()
- testValidateRegistrationData_InvalidVehicleNumber()
- testValidateInvitationData()
- testValidateApprovalPermission()
```

---

### TDD-015: [GREEN] 实现VisitorValidationService
**依赖**: [TDD-014]
**估时**: 50分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorValidationService`
2. 实现数据验证逻辑
3. 实现业务规则验证
4. 确保所有测试通过

**验收标准**:
- [ ] 所有验证测试通过
- [ ] 业务逻辑正确实现
- [ ] 异常处理恰当
- [ ] PHPStan检查通过

**实现要点**:
- 手机号格式验证（中国大陆）
- 预约时间不能早于当前时间
- 车牌号格式验证（可选）
- 权限验证逻辑

---

### TDD-016: [REFACTOR] 优化验证逻辑
**依赖**: [TDD-015]
**估时**: 20分钟
**类型**: REFACTOR

**描述**:
1. 重构验证方法结构
2. 提取通用验证逻辑
3. 优化异常消息
4. 添加验证规则文档

**验收标准**:
- [ ] 所有测试通过
- [ ] 验证逻辑更清晰
- [ ] 异常消息更友好
- [ ] 代码可维护性提升

---

### TDD-017: [RED] 创建VisitorLogService测试
**依赖**: [TDD-016]
**估时**: 30分钟
**类型**: RED

**描述**:
1. 创建 `VisitorLogServiceTest`
2. 编写日志记录测试
3. 编写错误日志测试
4. 编写批量日志测试

**验收标准**:
- [ ] 日志服务测试失败（红色状态）
- [ ] 覆盖所有日志场景
- [ ] 测试日志格式和内容
- [ ] 包含性能测试

**测试场景**:
```php
- testLogAction_Success()
- testLogError_WithVisitor()
- testLogError_WithoutVisitor()
- testBatchLogActions()
- testLogActionTypes()
```

---

### TDD-018: [GREEN] 实现VisitorLogService
**依赖**: [TDD-017]
**估时**: 40分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorLogService`
2. 实现日志记录功能
3. 实现错误日志记录
4. 实现批量日志操作

**验收标准**:
- [ ] 所有日志测试通过
- [ ] 日志记录准确
- [ ] 支持批量操作
- [ ] 性能优化

---

### TDD-019: [RED] 创建VisitorRegistrationService测试
**依赖**: [TDD-018]
**估时**: 60分钟
**类型**: RED

**描述**:
1. 创建 `VisitorRegistrationServiceTest`
2. 编写访客注册测试
3. 编写访客更新测试
4. 编写访客删除测试
5. 编写集成测试（与其他服务的交互）

**验收标准**:
- [ ] 注册服务测试失败（红色状态）
- [ ] 覆盖所有业务方法
- [ ] 包含事件发布测试
- [ ] 模拟外部依赖

**测试场景**:
```php
- testRegister_Success()
- testRegister_ValidationError()
- testRegister_WithBizUser()
- testRegister_WithoutBizUser()
- testUpdateVisitor_Success()
- testUpdateVisitor_NotFound()
- testDeleteVisitor_Success()
- testGetVisitor_Found()
- testGetVisitor_NotFound()
- testEventDispatching()
- testLogRecording()
```

---

### TDD-020: [GREEN] 实现VisitorRegistrationService
**依赖**: [TDD-019]
**估时**: 75分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorRegistrationService`
2. 实现访客注册逻辑
3. 实现访客管理功能
4. 集成验证和日志服务
5. 实现事件发布

**验收标准**:
- [ ] 所有注册服务测试通过
- [ ] 业务逻辑完整实现
- [ ] 事件系统集成
- [ ] 异常处理恰当

**实现要点**:
- 使用VisitorValidationService进行数据验证
- 使用VisitorLogService记录操作日志
- 发布VisitorRegisteredEvent事件
- 正确处理BizUser关联

---

### TDD-021: [REFACTOR] 优化VisitorRegistrationService
**依赖**: [TDD-020]
**估时**: 30分钟
**类型**: REFACTOR

**描述**:
1. 重构服务方法结构
2. 优化事务处理
3. 提升错误处理
4. 优化性能

**验收标准**:
- [ ] 所有测试通过
- [ ] 代码结构更清晰
- [ ] 事务处理更安全
- [ ] 性能有所提升

---

### TDD-022: [RED] 创建VisitorInvitationService测试
**依赖**: [TDD-021]
**估时**: 50分钟
**类型**: RED

**描述**:
1. 创建 `VisitorInvitationServiceTest`
2. 编写邀请创建测试
3. 编写邀请状态管理测试
4. 编写邀请过期处理测试

**验收标准**:
- [ ] 邀请服务测试失败（红色状态）
- [ ] 覆盖邀请全生命周期
- [ ] 测试邀请码唯一性
- [ ] 包含权限验证测试

**测试场景**:
```php
- testCreateInvitation_Success()
- testCreateInvitation_InvalidInviter()
- testConfirmInvitation_Success()
- testConfirmInvitation_Expired()
- testRejectInvitation_Success()
- testCancelInvitation_Success()
- testGetInvitation_Found()
- testGetInvitation_NotFound()
- testInviteCodeUniqueness()
```

---

### TDD-023: [GREEN] 实现VisitorInvitationService
**依赖**: [TDD-022]
**估时**: 65分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorInvitationService`
2. 实现邀请管理逻辑
3. 实现邀请码生成
4. 实现过期处理机制

**验收标准**:
- [ ] 所有邀请服务测试通过
- [ ] 邀请流程完整
- [ ] 邀请码唯一性保证
- [ ] 过期机制正确

**实现要点**:
- 生成唯一邀请码算法
- 邀请状态状态机
- 与VisitorRegistrationService集成
- 事件发布机制

---

### TDD-024: [RED] 创建VisitorApprovalService测试
**依赖**: [TDD-023]
**估时**: 45分钟
**类型**: RED

**描述**:
1. 创建 `VisitorApprovalServiceTest`
2. 编写审批提交测试
3. 编写审批决策测试
4. 编写权限验证测试

**验收标准**:
- [ ] 审批服务测试失败（红色状态）
- [ ] 覆盖审批全流程
- [ ] 包含权限控制测试
- [ ] 测试审批规则

**测试场景**:
```php
- testSubmitForApproval_Success()
- testSubmitForApproval_AlreadySubmitted()
- testApproveVisitor_Success()
- testApproveVisitor_NotAuthorized()
- testRejectVisitor_Success()
- testRejectVisitor_WithReason()
- testGetPendingApprovals()
- testApprovalPermissionValidation()
```

---

### TDD-025: [GREEN] 实现VisitorApprovalService
**依赖**: [TDD-024]
**估时**: 60分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorApprovalService`
2. 实现审批流程逻辑
3. 实现权限验证
4. 集成BizUser权限系统

**验收标准**:
- [ ] 所有审批服务测试通过
- [ ] 审批流程正确
- [ ] 权限控制有效
- [ ] 与权限系统集成

---

### TDD-026: [RED] 创建VisitorPassService测试
**依赖**: [TDD-025]
**估时**: 40分钟
**类型**: RED

**描述**:
1. 创建 `VisitorPassServiceTest`
2. 编写通行码生成测试
3. 编写通行码验证测试
4. 编写通行码使用测试

**验收标准**:
- [ ] 通行码服务测试失败（红色状态）
- [ ] 覆盖通行码全生命周期
- [ ] 测试二维码生成（TODO）
- [ ] 包含安全性测试

**测试场景**:
```php
- testGeneratePass_Success()
- testGeneratePass_VisitorNotApproved()
- testValidatePass_Valid()
- testValidatePass_Expired()
- testValidatePass_NotFound()
- testUsePass_Success()
- testUsePass_AlreadyUsed()
- testIsPassValid_True()
- testIsPassValid_False()
- testPassCodeUniqueness()
```

---

### TDD-027: [GREEN] 实现VisitorPassService
**依赖**: [TDD-026]
**估时**: 55分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorPassService`
2. 实现通行码生成逻辑
3. 实现验证和使用逻辑
4. 预留二维码生成接口

**验收标准**:
- [ ] 所有通行码服务测试通过
- [ ] 通行码生成唯一
- [ ] 验证逻辑准确
- [ ] 安全性要求满足

**实现要点**:
- 通行码格式：前缀 + 时间戳 + 随机数
- 有效期配置（环境变量）
- 预留QrCodeGeneratorInterface接口
- 使用记录和状态管理

---

### TDD-028: [RED] 创建VisitorQueryService测试
**依赖**: [TDD-027]
**估时**: 50分钟
**类型**: RED

**描述**:
1. 创建 `VisitorQueryServiceTest`
2. 编写搜索功能测试
3. 编写统计功能测试
4. 编写分页查询测试

**验收标准**:
- [ ] 查询服务测试失败（红色状态）
- [ ] 覆盖所有查询场景
- [ ] 测试性能要求
- [ ] 包含数据导出测试

**测试场景**:
```php
- testSearchVisitors_ByCriteria()
- testSearchVisitors_EmptyResult()
- testGetVisitorsByStatus_WithPagination()
- testGetVisitorStatistics_ByDateRange()
- testGetVisitorStatistics_EmptyData()
- testGetVisitorHistory_Complete()
- testSearchPerformance()
```

---

### TDD-029: [GREEN] 实现VisitorQueryService
**依赖**: [TDD-028]
**估时**: 65分钟
**类型**: GREEN

**描述**:
1. 实现 `VisitorQueryService`
2. 实现搜索查询逻辑
3. 实现统计分析功能
4. 优化查询性能

**验收标准**:
- [ ] 所有查询服务测试通过
- [ ] 搜索功能完整
- [ ] 统计数据准确
- [ ] 查询性能优化

**实现要点**:
- 使用Repository进行数据查询
- 实现复杂条件组合查询
- 分页和排序支持
- 统计数据缓存策略（预留）

---

### TDD-030: [REFACTOR] 优化所有Service设计
**依赖**: [TDD-029]
**估时**: 45分钟
**类型**: REFACTOR

**描述**:
1. 重构所有Service类的代码结构
2. 统一异常处理策略
3. 优化服务间协作
4. 提升整体性能

**验收标准**:
- [ ] 所有测试通过
- [ ] 代码结构统一
- [ ] 异常处理规范
- [ ] 服务协作更高效

---

## 第四阶段：事件系统和扩展

### TDD-031: [RED] 创建事件类测试
**依赖**: [TDD-030]
**估时**: 30分钟
**类型**: RED

**描述**:
1. 创建事件类测试
2. 编写事件数据测试
3. 编写事件序列化测试

**验收标准**:
- [ ] 事件类测试失败（红色状态）
- [ ] 测试事件数据完整性
- [ ] 包含事件版本兼容性测试

**测试场景**:
```php
VisitorRegisteredEventTest:
- testEventCreation()
- testEventData()
- testSerialization()

VisitorApprovedEventTest:
- testApprovalEventData()
- testApproverInformation()

PassUsedEventTest:
- testPassUsageData()
- testTimestampAccuracy()
```

---

### TDD-032: [GREEN] 实现事件类
**依赖**: [TDD-031]
**估时**: 40分钟
**类型**: GREEN

**描述**:
1. 实现所有领域事件类
2. 实现事件数据结构
3. 确保事件序列化支持

**验收标准**:
- [ ] 所有事件测试通过
- [ ] 事件数据结构正确
- [ ] 支持序列化和反序列化

---

### TDD-033: [RED] 创建事件监听器测试
**依赖**: [TDD-032]
**估时**: 35分钟
**类型**: RED

**描述**:
1. 创建事件监听器测试
2. 编写自动化流程测试
3. 编写事件链测试

**验收标准**:
- [ ] 事件监听器测试失败（红色状态）
- [ ] 测试事件处理逻辑
- [ ] 包含异常处理测试

---

### TDD-034: [GREEN] 实现事件监听器
**依赖**: [TDD-033]
**估时**: 45分钟
**类型**: GREEN

**描述**:
1. 实现事件监听器
2. 实现自动化业务流程
3. 集成事件系统

**验收标准**:
- [ ] 所有事件监听器测试通过
- [ ] 事件处理正确
- [ ] 业务流程自动化

**实现要点**:
- 访客注册后自动提交审批
- 审批通过后自动生成通行码
- 操作日志自动记录

---

### TDD-035: [RED] 创建扩展接口测试
**依赖**: [TDD-034]
**估时**: 25分钟
**类型**: RED

**描述**:
1. 创建QrCodeGeneratorInterface测试
2. 创建EncryptionServiceInterface测试
3. 编写接口契约测试

**验收标准**:
- [ ] 扩展接口测试失败（红色状态）
- [ ] 测试接口契约
- [ ] 包含默认实现测试

---

### TDD-036: [GREEN] 实现扩展接口
**依赖**: [TDD-035]
**估时**: 35分钟
**类型**: GREEN

**描述**:
1. 实现扩展接口
2. 实现默认实现类
3. 配置依赖注入

**验收标准**:
- [ ] 所有扩展接口测试通过
- [ ] 默认实现工作正常
- [ ] 依赖注入配置正确

---

## 第五阶段：Bundle集成和配置

### TDD-037: [RED] 创建Bundle集成测试
**依赖**: [TDD-036]
**估时**: 40分钟
**类型**: RED

**描述**:
1. 创建Bundle注册测试
2. 创建服务配置测试
3. 创建BizUser集成测试

**验收标准**:
- [ ] Bundle集成测试失败（红色状态）
- [ ] 测试服务注册
- [ ] 测试外部依赖集成

---

### TDD-038: [GREEN] 实现Bundle集成
**依赖**: [TDD-037]
**估时**: 50分钟
**类型**: GREEN

**描述**:
1. 实现VisitorManageBundle类
2. 配置服务容器
3. 实现BizUser Bundle集成

**验收标准**:
- [ ] 所有Bundle集成测试通过
- [ ] 服务自动装配工作
- [ ] 外部依赖正确集成

---

### TDD-039: [RED] 创建环境配置测试
**依赖**: [TDD-038]
**估时**: 20分钟
**类型**: RED

**描述**:
1. 创建环境变量配置测试
2. 编写配置验证测试
3. 编写默认值测试

**验收标准**:
- [ ] 配置测试失败（红色状态）
- [ ] 测试所有配置项
- [ ] 包含配置验证

---

### TDD-040: [GREEN] 实现环境配置
**依赖**: [TDD-039]
**估时**: 30分钟
**类型**: GREEN

**描述**:
1. 实现配置读取逻辑
2. 添加配置验证
3. 设置合理默认值

**验收标准**:
- [ ] 所有配置测试通过
- [ ] 配置读取正确
- [ ] 默认值合理

---

## 第六阶段：集成测试和文档

### TDD-041: [RED] 创建端到端集成测试
**依赖**: [TDD-040]
**估时**: 60分钟
**类型**: RED

**描述**:
1. 创建完整业务流程测试
2. 编写多服务协作测试
3. 编写性能基准测试

**验收标准**:
- [ ] 集成测试失败（红色状态）
- [ ] 覆盖核心业务场景
- [ ] 包含性能基准

**测试场景**:
```php
VisitorManagementIntegrationTest:
- testCompleteVisitorFlow() // 注册->审批->生成通行码->验证->使用
- testInvitationFlow() // 邀请->确认->审批->通行码
- testBulkOperations() // 批量注册和处理
- testEventChain() // 事件链完整性
- testPermissionControl() // 权限控制
- testDataConsistency() // 数据一致性
- testPerformanceBenchmark() // 性能基准
```

---

### TDD-042: [GREEN] 完善集成实现
**依赖**: [TDD-041]
**估时**: 45分钟
**类型**: GREEN

**描述**:
1. 修复集成测试发现的问题
2. 完善服务间协作
3. 优化整体性能

**验收标准**:
- [ ] 所有集成测试通过
- [ ] 业务流程完整
- [ ] 性能达到基准要求

---

### TDD-043: [REFACTOR] 最终代码优化
**依赖**: [TDD-042]
**估时**: 60分钟
**类型**: REFACTOR

**描述**:
1. 全面代码审查和重构
2. 统一代码风格和规范
3. 性能优化和内存优化
4. 文档注释完善

**验收标准**:
- [ ] 所有测试通过
- [ ] 代码质量达到最高标准
- [ ] 性能最优化
- [ ] 文档完整

---

### TDD-044: [文档] 创建API文档和使用指南
**依赖**: [TDD-043]
**估时**: 90分钟
**类型**: Documentation

**描述**:
1. 生成API文档
2. 编写使用指南
3. 创建集成示例
4. 编写配置说明

**验收标准**:
- [ ] API文档完整准确
- [ ] 使用指南清晰
- [ ] 示例代码可运行
- [ ] 配置说明详细

---

## 质量保证检查点

### 每个任务完成后必须执行：

```bash
# 1. PHPStan静态分析（Level 8，零错误）
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/visitor-manage-bundle/src packages/visitor-manage-bundle/tests

# 2. PHPUnit测试（100%通过，无跳过）
./vendor/bin/phpunit packages/visitor-manage-bundle/tests --stop-on-failure

# 3. 代码覆盖率检查（≥90%）
./vendor/bin/phpunit packages/visitor-manage-bundle/tests --coverage-text --coverage-clover coverage.xml

# 4. 代码风格检查
./vendor/bin/php-cs-fixer fix packages/visitor-manage-bundle/src --dry-run --diff

# 5. 偷懒行为检测
grep -rE "(markTestSkipped|assertTrue\(true\)|TODO|FIXME)" packages/visitor-manage-bundle/tests/ && echo "❌ 发现偷懒行为" && exit 1
```

### 阶段性检查点：

1. **第一阶段完成**：基础设施100%就绪，实体和Repository测试覆盖率≥95%
2. **第二阶段完成**：DTO和异常类完整，验证逻辑无漏洞
3. **第三阶段完成**：核心服务功能完整，业务逻辑测试覆盖率≥95%
4. **第四阶段完成**：事件系统正常，扩展机制可用
5. **第五阶段完成**：Bundle集成成功，配置系统完整
6. **第六阶段完成**：端到端测试通过，文档齐全

## 估时总结

| 阶段 | 任务数 | 总估时 | 主要内容 |
|------|--------|--------|----------|
| 第一阶段 | TDD-001 ~ TDD-009 | ~8小时 | 实体、Repository基础设施 |
| 第二阶段 | TDD-010 ~ TDD-013 | ~2小时 | DTO和异常类 |
| 第三阶段 | TDD-014 ~ TDD-030 | ~14小时 | 核心业务服务 |
| 第四阶段 | TDD-031 ~ TDD-036 | ~3.5小时 | 事件系统和扩展 |
| 第五阶段 | TDD-037 ~ TDD-040 | ~2.5小时 | Bundle集成和配置 |
| 第六阶段 | TDD-041 ~ TDD-044 | ~4小时 | 集成测试和文档 |
| **总计** | **44个任务** | **~34小时** | **完整Package实现** |

## 执行建议

1. **严格按顺序执行**：任务间存在依赖关系，不可跳跃
2. **每日检查点**：建议每天完成1-2个阶段，及时检查质量
3. **并行开发**：无依赖关系的任务（如不同实体的测试）可以并行进行
4. **持续重构**：在GREEN阶段后立即进行REFACTOR，不要累积技术债务
5. **文档同步**：在开发过程中持续更新文档，不要等到最后

此任务分解确保了完整的TDD开发流程，保证代码质量和测试覆盖率达到Package标准要求。每个任务都有明确的验收标准和质量门禁，确保最终交付的是生产就绪的高质量代码。