# SugarCRM数据库版本管理系统

Database Version Control System

## Install

运行`dbvcs.sql`

```sql
CREATE TABLE `db_versions` (
  `id` int(10) unsigned NOT NULL,
  `sha1` char(40) NOT NULL,
  `date_entered` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`sha1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**请勿随意修改 db_versions 数据，除非你已经非常清楚自己的操作会产生什么影响**

## Run

TODO

## MySQL backup & restore

```sh
DBNAME="sugarcrm"
mysqldump "$DBNAME" --routines --triggers -d > "$DBNAME".sql
mysqldump "$DBNAME" acl_actions config email_templates fields_meta_data relationships versions >> "$DBNAME".sql
```

如果恢复的时候有函数、存储过程，需要先运行以下命令：

```sql
SET GLOBAL log_bin_trust_function_creators = 1;
```

## Ignore tables

由于以下表在代码中既未找到，也没在触发器、存储过程中找到，故暂不考虑纳入同步计划。

- Audit_income
- ERP_NEW_OLD_BLBH
- OP_BackGroupOperation_Log
- OP_JobDetailLog
- OP_JobLog
- REMOTE_ERP_ORDER
- REMOTE_ERP_ORDERS
- REMOTE_ERP_ORDER_ITEM
- Report_aggregate_case_first_3d
- Sync_Flag
- bank_info_for_financial
- customer_holiday
- department_for_financial
- ea_design_bk
- erp_emp_bljc
- erp_emp_price
- group_modify_info
- sync_partner_case_report
- user_duty
- users_copy

## References

- [Yaf(Yet Another Framework)用户手册](http://www.laruence.com/manual/)
- [MySQL 5.5 Reference Manual](https://dev.mysql.com/doc/refman/5.5/en/)
- [bootcdn](https://www.bootcdn.cn/)
- [Font Awesome Icons 4.7](https://fontawesome.com/v4.7.0/icons/)