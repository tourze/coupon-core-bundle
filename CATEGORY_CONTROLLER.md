# EasyAdmin 优惠券分类控制器

## 概述

为优惠券分类实体创建了完整的EasyAdmin后台管理界面，支持树形结构管理和丰富的功能特性。

### 1. CategoryCrudController

**文件位置**: `src/Controller/Admin/CategoryCrudController.php`

**核心特性**:
- 支持树形结构的分类管理
- 富文本编辑器集成
- 图片上传和显示
- JSON数组字段处理
- 关联数据预加载优化
- 层级标题显示

### 2. 字段配置详解

#### 基础字段
- **id**: 自增ID，仅列表页显示
- **title**: 分类名称（必填，最大60字符）
- **parent**: 上级分类（自关联，支持自动完成）

#### 内容字段
- **logoUrl**: LOGO图片（支持上传，带预览）
- **description**: 富文本简介（使用TextEditorField）
- **remark**: 备注（普通文本，最大100字符）

#### 特殊字段
- **showTags**: 显示标签（JSON数组，多选下拉框）
- **nestTitle**: 层级标题（仅列表显示，显示完整路径）

#### 时间和排序
- **startTime/endTime**: 有效时间范围
- **sortNumber**: 排序值（数值越大越靠前）
- **valid**: 有效状态

#### 关联信息
- **children**: 下级分类集合（详情页展示）
- **coupons**: 关联优惠券集合（详情页展示）

### 3. 树形结构支持

#### 实体注解
```php
#[TreeView(dataModel: Category::class, targetAttribute: 'parent')]
```

#### 查询优化
- 预加载parent和children关联数据
- 按sortNumber和id降序排列
- 避免N+1查询问题

#### 层级显示
- `nestTitle`方法显示完整路径：`上级分类/当前分类`
- 支持无限层级嵌套
- 自动处理父子关系

### 4. 富文本编辑

#### 配置
```php
yield TextEditorField::new('description', '简介')
    ->setRequired(false)
    ->hideOnIndex()
    ->setColumns(12)
    ->setHelp('分类的详细描述，支持富文本编辑');
```

#### 特性
- 支持HTML格式化
- 在模板中自动去除HTML标签显示摘要
- 完整的WYSIWYG编辑体验

### 5. 图片处理

#### 上传配置
```php
yield ImageField::new('logoUrl', 'LOGO地址')
    ->setBasePath('/uploads')
    ->setUploadDir('public/uploads')
    ->setUploadedFileNamePattern('[year]/[month]/[day]/[contenthash].[extension]')
```

#### 特性
- 按年月日目录结构保存
- 使用内容哈希命名避免冲突
- 支持预览和替换
- 在详情模板中缩略图显示

### 6. JSON字段处理

#### showTags字段
```php
yield ChoiceField::new('showTags', '显示标签')
    ->setRequired(false)
    ->allowMultipleChoices()
    ->autocomplete()
    ->setChoices($this->getShowTagsChoices());
```

#### 注意事项
- 实体中定义为`#[SelectField(targetEntity: 'product.tag.fetcher', mode: 'multiple')]`
- 控制器中需要实现`getShowTagsChoices()`方法
- TODO: 需要集成真实的标签获取服务

### 7. 模板文件

#### children.html.twig
**功能**: 显示下级分类
**特性**:
- 卡片式布局
- 显示分类基本信息
- 状态标识
- 缩略图预览
- 排序值显示

#### coupons.html.twig
**功能**: 显示关联优惠券
**特性**:
- 优惠券基本信息展示
- 状态和有效期显示
- 图标预览
- 统计信息

### 8. 过滤器配置

支持的过滤器：
- **文本过滤**: 分类名称
- **实体过滤**: 上级分类
- **布尔过滤**: 有效状态
- **数值过滤**: 排序值
- **时间过滤**: 创建时间、更新时间、有效时间

### 9. 查询优化

#### createIndexQueryBuilder方法
```php
public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
{
    return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
        ->leftJoin('entity.parent', 'parent')
        ->addSelect('parent')
        ->leftJoin('entity.children', 'children')
        ->addSelect('children')
        ->orderBy('entity.sortNumber', 'DESC')
        ->addOrderBy('entity.id', 'DESC');
}
```

#### 优化效果
- 减少数据库查询次数
- 预加载关联数据
- 优化排序性能

### 10. 使用场景

#### 分类管理
1. **新建分类**: 选择上级分类，设置基本信息
2. **编辑分类**: 修改名称、描述、图片等
3. **层级调整**: 通过修改parent字段调整分类层级
4. **排序管理**: 通过sortNumber控制显示顺序

#### 数据查看
1. **树形浏览**: 通过层级标题了解分类结构
2. **关联查看**: 在详情页查看下级分类和关联优惠券
3. **状态管理**: 快速查看和切换分类状态

### 11. 扩展建议

#### 功能扩展
1. **拖拽排序**: 实现可视化的排序调整
2. **批量操作**: 批量修改状态、移动分类
3. **导入导出**: Excel格式的批量导入导出
4. **权限控制**: 基于角色的分类管理权限

#### 界面优化
1. **树形视图**: 专门的树形管理界面
2. **图标库**: 集成图标选择器
3. **预览功能**: 分类展示效果预览
4. **统计面板**: 分类使用统计和分析

### 12. 注意事项

#### 数据完整性
- 删除分类前检查是否有下级分类
- 删除分类前检查是否有关联优惠券
- 避免循环引用（父分类设为自己的子分类）

#### 性能优化
- 大量分类时考虑分页
- 深层嵌套时优化查询策略
- 缓存常用的分类树结构

#### 用户体验
- 提供清晰的层级指示
- 智能的默认排序
- 友好的错误提示 