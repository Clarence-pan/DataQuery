# Introduce 介绍
SQL style query utils for PHP.
为PHP写的SQL风格的查询工具。

# Backgroud 背景
In my projects, I've found a lot of codes are for transforming data formats. But, I've NOT found simple ways to deal the following problems in PHP. 
在我的项目中，我发现有很多数据是做数据格式转换的。但是我却没有能在PHP中找到一些简单的方法来解决以下问题：

 - Only partial columns of data are being processed, when the data source returned a lot of columns;
 - 有个外部数据源返回了许多数据，但是只有其中的某几列是需要处理的数据；
 - A more proper column name is needed to match the context;
 - 为了更加贴合上下文语义，需要给列换个名字；
 - In order to keep efficience in the later processing, the table data should be converted to a key-value map structure whose key is a specific column.
 - 为了后续处理的效率，希望将数据表转换为以某一列为索引的key-value的映射结构；

So I begin this project to make things easy and efficient.
所以有了这个项目，希望能让事情变得简单、高效。

# Examples 示例
  假如有个数据源有productId和productName两列，而希望将productId大于5的数据提出取出来，并且组织成productName => [ xxx ]的形式，则可以这样写：
```php
  $data = DataQuery::from($dataSource)
           ->select(array('productId' => 'id', 'productName'))
           ->where(function ($row) {
               return $row['id'] > 5;
           }, DataQuery::CONDITION_CUSTOM)
           ->orderBy('id', SORT_DESC)
           ->indexedBy('productName')
           ->toArray();
```
  具体实例可以参考DataQueryTest.php

# Contribute 贡献
Welcome to contribute your ideas to this project.
欢迎贡献你的想法。
