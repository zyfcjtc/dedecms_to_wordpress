# 织梦CMS转wordpress详细步骤
## 导出织梦Dedecms全站RSS文件
### 步骤
1. 在dedecms的dede文件夹（后台文件夹）下找到`makehtml_rss_action.php`文件，对其进行编辑，找到代码：
    ```php
    echo "完成所有文件更新！";
    ```
      在其下面添加代码：
    ```php
    echo "<a href='/rss.xml' target='_blank'>浏览…</a>";
    ```
    作用是在生成rss.xml文件后方便点击查看生成结果。
2. 在dedecms的`include`文件夹下找到`arc.rssview.class.php`文件，并对其进行编辑，找到代码：
    ```php
    $murl = $GLOBALS['cfg_cmspath']."/data/rss/".$this->TypeID.".xml";
    ```
    修改为：
    ```php
    $murl = $GLOBALS['cfg_cmspath']."/rss.xml";
    ```
    作用是修改生成的`rss.xml`文件路径，让该文件保存在网站根目录
    
    再向下找到以下代码并将其删除：
    ```php
    $orwhere .= "And (arc.typeid in (".GetSonIds($this->TypeID,$this->TypeFields['channeltype']).") )";
    ```
3.  进入后台——生成——更新RSS文件，把“单个类目最大记录数”改为全站的文章数量，然后点击“开始更新”，更新完成后，网站根目录下会生成一个全站rss.xml文件，可通过 `http://网站域名/rss.xml`     查看。
    附：如果生成的rss.xml文件有错误，在根目录下的templets文件夹中的plus文件夹里找到rss.htm文件，对其进行编辑，找到代码：
    ```php
    <description><![CDATA[[field:description function='html2text(@me)'/]]]></description>
    ```
    修改为：
    ```php
    <description><![CDATA[[field:description/]]]></description>
    ```
## 导入织梦Dedecms全站RSS文件
注：wordpress的编码是utf8，如果dedecms使用的不是utf8的，导入前请先转换编码为utf8。
具体如何查看：
找到`common.inc.php`文件，里面的
```php
$cfg_version = 
$cfg_soft_lang = 
$cfg_soft_public = 
```
即可知道是什么编码
### 导入织梦RSS步骤
1.  织梦DedeCMS文章标题等基本数据导入wordpress站点
    进入wordpress后台admin => 工具 => 导入 => RSS, 上传导入生成的织梦全站RSS文件。
    导入过程中有可能会超时，重新上传RSS即可，不会重复导入。
    
