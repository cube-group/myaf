# LFile-简单文件处理和文件上传封装
### LFile的使用
```
use libs\Log\LLog;
require 'autoloader.php';


```


# LExcel-PHPExcel工具类库封装类
### 创建Excel
```
use libs\Excel\LExcel;
require 'autoloader.php';

$excelFileName = '/data/file/newExcel.xlsx';
$data = [
    ['id','name','password','address','phone','mail'],
    ['id','name','password','address','phone','mail'],
    ['id','name','password','address','phone','mail'],
    ['id','name','password','address','phone','mail'],
    ['id','name','password','address','phone','mail']
];
$title = '新的excel';
//[$title]会按照sheet0->sheetn自动导入
$result = LExcel::create($excelFileName,[$data],[$title]);
if($result){
    echo 'success';
}else{
    echo 'failed';
}
```
### 解析Excel
```
use libs\Excel\LExcel;
require 'autoloader.php';

$excelFileName = '/data/file/newExcel.xlsx';
//[0]会将excel的sheet-0解析出来
$excelArray = LExcel::read($excelFileName,[0]);
foreach($excelArray as $row){
    foreach($row as $column){
        echo $column;
    }
}
```