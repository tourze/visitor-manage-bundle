# Visitor Manage Bundle - 需求规范

## 概述

`visitor-manage-bundle` 是一个为 Symfony 应用提供访客管理功能的包。它简化企业园区、办公楼等场所的访客登记、预约和通行管理流程。

### 核心价值主张

- 提供访客登记、预约和通行管理
- 支持访客邀请和现场登记
- 与 `biz-user-bundle` 集成，实现统一用户管理

## 功能需求

### FR1 - 访客登记管理

#### FR1.1 访客自助登记
- 系统必须提供访客自助登记接口，支持姓名、手机号、拜访公司、预约时间、拜访原因等信息录入
- 当访客提交登记信息时，系统必须验证必填字段完整性和数据格式有效性
- 如果访客信息验证失败，系统必须返回具体的错误提示信息
- 系统必须支持访客车辆信息登记，包括车牌号码

#### FR1.2 访客信息验证
- 系统必须验证访客手机号格式的正确性
- 系统必须验证预约时间不能早于当前时间
- 访客信息与 `biz-user-bundle` 中的 BizUser 实体关联，共享用户基础信息

### FR2 - 访客邀请管理

#### FR2.1 企业邀请访客
- 系统必须提供企业员工邀请访客的接口
- 当企业员工创建访客邀请时，系统必须记录邀请人信息、被邀请人信息和接待安排
- 系统必须为邀请访客生成唯一的邀请标识
- 邀请人通过 BizUser 实体标识，确保只有系统内用户可发起邀请

#### FR2.2 邀请状态管理
- 系统必须跟踪邀请状态（待确认、已确认、已拒绝、已过期）
- 当邀请过期时，系统必须自动更新邀请状态
- 系统必须支持邀请的撤销和重新发送

### FR3 - 访客审批管理

#### FR3.1 审批流程
- 系统必须提供访客申请的简单审批工作流
- 当访客提交登记申请时，系统必须根据配置的审批规则分配审批人
- 如果审批被拒绝，系统必须记录拒绝原因并通知访客
- 审批人必须是 BizUser 中的用户

#### FR3.2 审批权限管理
- 系统必须验证审批人的权限范围
- 当审批人超出权限范围操作时，系统必须拒绝操作并返回权限错误

### FR4 - 通行码管理

#### FR4.1 通行码生成
- 当访客审批通过时，系统必须生成唯一的通行码
- 系统必须支持二维码格式
- 通行码必须包含访客基本信息和有效期
- 系统必须确保通行码的唯一性

#### FR4.2 通行码验证
- 系统必须提供通行码验证接口
- 当通行码过期时，系统必须拒绝验证并返回过期提示
- 系统必须记录通行码的使用历史

### FR5 - 访客查询管理

#### FR5.1 访客记录查询
- 系统必须提供访客记录查询功能
- 系统必须支持按访客姓名、手机号、拜访公司、预约时间等条件查询
- 系统必须支持分页查询

#### FR5.2 访客统计
- 系统必须提供访客数量统计功能
- 系统必须支持按时间段、状态等维度统计
- 如果统计数据为空，系统必须返回空结果而非错误信息


## 非功能需求

### NFR1 - 安全要求
- 系统必须对敏感信息进行加密存储
- 敏感信息包括：手机号码、车牌号码等
- 通过集成 biz-user-bundle 的权限系统，确保只有授权人员可以查看访客信息
- 系统必须支持数据加密传输，所有API通信必须使用HTTPS协议

### NFR2 - 可用性要求
- 系统必须保证核心功能的稳定运行
- 核心功能包括：访客登记、通行码验证


## 集成需求

### IR1 - 框架集成
- 包必须与 Symfony 6.4+ 完全兼容
- 包必须使用 Symfony 的依赖注入容器进行服务管理
- 包必须支持 Doctrine ORM 进行数据持久化
- 包必须集成 biz-user-bundle 进行用户管理和权限控制

### IR2 - BizUser Bundle 集成
- 访客实体必须与 BizUser 实体关联
- 邀请人、审批人必须是 BizUser 类型
- 复用 BizUser 的权限和角色系统

### IR3 - 数据库集成
- 系统必须支持 MySQL 8.0+、PostgreSQL 13+ 数据库
- 使用 Doctrine ORM 进行数据持久化

## 验收标准

### AS1 - 测试覆盖率
- 单元测试覆盖率必须达到90%以上
- 集成测试必须覆盖所有主要业务流程

### AS2 - 文档完整性
- 必须提供完整的 API 文档
- 必须提供安装和配置指南

### AS3 - 代码质量
- 代码必须通过 PHPStan Level 8 静态分析
- 代码必须符合 PSR-12 编码规范
- 所有公共接口必须包含完整的类型声明

### AS4 - 兼容性验证
- 必须在 PHP 8.1+ 版本上测试通过
- 必须在 Symfony 6.4+ 版本上测试通过
- 必须支持 MySQL 8.0+、PostgreSQL 13+ 数据库

## 约束条件

### C1 - 技术约束
- 必须使用 PHP 8.1+ 语言特性
- 必须遵循 Symfony 最佳实践
- 不得使用已弃用的 PHP 特性和 Symfony 组件

### C2 - 业务约束
- 访客信息保留期限不得超过法律法规要求
- 必须支持访客信息的删除和匿名化处理

## 实体设计

### ED1 - 核心实体

#### Visitor（访客）
- id: int 主键
- bizUser: ManyToOne 关联 BizUser（可选，如果访客已注册）
- name: string 访客姓名
- mobile: string 手机号码
- company: string 拜访公司
- reason: text 拜访原因
- vehicleNumber: string 车牌号码（可选）
- appointmentTime: datetime 预约时间
- status: enum 状态（pending/approved/rejected/signed_in/signed_out）
- createdAt: datetime 创建时间
- updatedAt: datetime 更新时间

#### VisitorInvitation（访客邀请）
- id: int 主键
- inviter: ManyToOne 关联 BizUser（邀请人）
- visitor: OneToOne 关联 Visitor
- inviteCode: string 唯一邀请码
- status: enum 状态（pending/confirmed/rejected/expired）
- expireAt: datetime 过期时间
- createdAt: datetime 创建时间

#### VisitorApproval（访客审批）
- id: int 主键
- visitor: ManyToOne 关联 Visitor
- approver: ManyToOne 关联 BizUser（审批人）
- status: enum 状态（pending/approved/rejected）
- rejectReason: text 拒绝原因（可选）
- approvedAt: datetime 审批时间
- createdAt: datetime 创建时间

#### VisitorPass（访客通行码）
- id: int 主键
- visitor: OneToOne 关联 Visitor
- passCode: string 唯一通行码
- qrCode: text 二维码内容
- validFrom: datetime 有效开始时间
- validTo: datetime 有效结束时间
- usedAt: datetime 使用时间（可选）
- createdAt: datetime 创建时间

#### VisitorLog（访客日志）
- id: int 主键
- visitor: ManyToOne 关联 Visitor
- action: enum 操作类型（registered/approved/rejected/signed_in/signed_out）
- operator: ManyToOne 关联 BizUser（可选，操作人）
- remark: text 备注
- createdAt: datetime 创建时间

### D1 - 外部依赖
- Symfony Framework 6.4+
- Doctrine ORM 2.14+
- biz-user-bundle 用户管理包