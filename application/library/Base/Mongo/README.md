### LMongo Fast Connection.
* Mongo set the init config array.
```javascript
use \Base\Mongo\LMongo;

$mongo = new LMongo([
    'host'=>'127.0.0.1',
    'port'=>27017,
    'username'=>'root',
    'password'=>'password',
    'database'=>'root'
]);
```
* Mongo use.
```javascript
$mongo->model('collection')->find();
SQL:select * from collection;
$mongo->model('collection')->find(array('name'=>'hello'));
SQL:select * from collection where name="hello";
$mongo->model('collection')->find(array('name'=>'hello'),array('name','group'));
SQL:select name,group from collection where name="hello";
$mongo->model('collection')->find(array('$or'=>array('a'=>1,'b'=>2));
SQL:select * from collection where (a=1 or b=2);
$mongo->model('collection')->find(array('$and'=>array('a'=>1,'b'=>2));
SQL:select * from collection where (a=1 and b=2);
$mongo->model('collection')->find(array('$or'=>array('a'=>1,'b'=>2,'$and'=>array('c'=>3,'d'=>4)));
SQL:select * from collection where (a=1 or b=2 or (c=3 and d=4));
$mongo->model('collection')->find(array('$gt'=>array('c'=>4)));
SQL:select * from collection where c>4;
$mongo->model('collection')->find(array('$gte'=>array('c'=>4)));
SQL:select * from collection where c>=4;
$mongo->model('collection')->find(array('$lt'=>array('c'=>4)));
SQL:select * from collection where c<4;
$mongo->model('collection')->find(array('$lte'=>array('c'=>4)));
SQL:select * from collection where c<=4;

$mongo->model('collection')->findOne(array('name'=>'hello'));
SQL:select * from collection limit 0,1;

$mongo->model('collection')->find()->sort(array('name'=>1));
SQL:select * from collection order by name asc;
$mongo->model('collection')->find()->sort(array('name'=>-1));
SQL:select * from collection order by name desc;

$mongo->model('collection')->find()->skip(0)->limit(10);
SQL:select * from collection limit 0,10;

$mongo->model('collection')->update(array('name'=>'hello'),array('$set'=>array('a'=>1,'b'=>2));
SQL:update collection a=1,b=2 where name="hello";

$mongo->model('collection')->update(array('name'=>'hello'),array('$inc'=>array('a'=>1));
SQL:update collection a=a+1 where name="hello";

...$filter: $or/$and/$gt/$gte/$lt/$lte...
...more $ you need to google...$set/$inc/$unset/$push/$pop/$upsert...

$mongo->model('collection')->remove(['name'=>'hello']);
SQL:delete from collection where name="hello";

$mongo->model('collection')->insert(['name'=>'hello']);
SQL:insert into collection name values 'hello';

$mongo->model('collection')->insert(['name'=>'hello']);
SQL:insert into collection name values 'hello';

$mongo->model('collection')->save(['name'=>'hello']);
SQL:INSERT INTO collection (name) SELECT ('hello') FROM VISUAL WHERE NOT EXISTS (SELECT * FROM collection WHERE name="hello");
```